<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\Warehouse;
use App\Models\SaleDetail;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __invoke(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'product_report', Product::class);

        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        //get warehouses assigned to user
        $user = auth()->user();
        $warehouses = Warehouse::query()->when(!$user->is_all_warehouses, function ($query) use ($user) {
            $query->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        })->get(['id', 'name']);

        $array_warehouses_id = $warehouses->pluck('id')->toArray();

        $products = Product::query()->select('id', 'code', 'is_variant', 'unit_id', 'type')
            ->when($request->warehouse_id, function ($query) use ($request) {
                $query->whereHas('warehouses', function ($query) use ($request) {
                    $query->where('warehouse_id', $request->warehouse_id);
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereTranslationLike('name', "%{$request->search}%")
                    ->orWhere('code', 'LIKE', "%{$request->search}%");
            })->paginate($request->limit);

        $totalRows = $products->count();

        $product_details = [];
        $product_details = $products->map(function ($product) use ($view_records, $request, $array_warehouses_id) {
            $nestedData = [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'sold_amount' => SaleDetail::query()->with('sale')
                    ->where('product_id', $product->id)
                    ->when(!$view_records, function ($query) {
                        $query->whereHas('sale', function ($q) {
                            $q->where('user_id', Auth::user()->id);
                        });
                    })
                    ->when($request->warehouse_id, function ($query) use ($request) {
                        $query->whereHas('sale', function ($q) use ($request) {
                            $q->where('warehouse_id', $request->warehouse_id);
                        });
                    }, function ($query) use ($array_warehouses_id) {
                        $query->whereHas('sale', function ($q) use ($array_warehouses_id) {
                            $q->whereIn('warehouse_id', $array_warehouses_id);
                        });
                    })
                    ->whereBetween('date', [$request->from, $request->to])
                    ->sum('total')
            ];

            if ($product->type != 'is_service') {
                $lims_product_sale_data = SaleDetail::query()->select('sale_unit_id', 'quantity')
                    ->with('sale')
                    ->where('product_id', $product->id)
                    ->when(!$view_records, function ($query) {
                        $query->whereHas('sale', function ($q) {
                            $q->where('user_id', Auth::user()->id);
                        });
                    })
                    ->when($request->warehouse_id, function ($query) use ($request) {
                        $query->whereHas('sale', function ($q) use ($request) {
                            $q->where('warehouse_id', $request->warehouse_id);
                        });
                    }, function ($query) use ($array_warehouses_id) {
                        $query->whereHas('sale', function ($q) use ($array_warehouses_id) {
                            $q->whereIn('warehouse_id', $array_warehouses_id);
                        });
                    })
                    ->whereBetween('date', [$request->from, $request->to])
                    ->get();

                $sold_qty = $lims_product_sale_data->reduce(function ($carry, $product_sale) {
                    $unit = Unit::find($product_sale->sale_unit_id);
                    if ($unit->operator == '*') {
                        return $carry + $product_sale->quantity * $unit->operator_value;
                    } elseif ($unit->operator == '/') {
                        return $carry + $product_sale->quantity / $unit->operator_value;
                    }
                }, 0);

                $unit_shortname = Unit::where('id', $product->unit_id)->value('ShortName');

                $nestedData['sold_qty'] = $sold_qty . ' ' . ($unit_shortname ?? '');

                return $nestedData;
            } else {
                $sold_qty = SaleDetail::select('sale_unit_id', 'quantity')
                    ->with('sale')
                    ->where('product_id', $product->id)
                    ->when(!$view_records, function ($query) {
                        $query->whereHas('sale', function ($q) {
                            $q->where('user_id', Auth::user()->id);
                        });
                    })
                    ->when($request->warehouse_id, function ($query) use ($request) {
                        $query->whereHas('sale', function ($q) use ($request) {
                            $q->where('warehouse_id', $request->warehouse_id);
                        });
                    }, function ($query) use ($array_warehouses_id) {
                        $query->whereHas('sale', function ($q) use ($array_warehouses_id) {
                            $q->whereIn('warehouse_id', $array_warehouses_id);
                        });
                    })
                    ->whereBetween('date', [$request->from, $request->to])
                    ->sum('quantity');

                $nestedData['sold_qty'] = $sold_qty;

                return $nestedData;
            }
        });

        return response()->json([
            'products' => $product_details,
            'totalRows' => $totalRows,
            'warehouses' => $warehouses,
        ]);
    }
}
