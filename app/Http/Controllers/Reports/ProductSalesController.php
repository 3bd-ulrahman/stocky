<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\SaleDetail;
use App\Models\Unit;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Models\Client;
use Illuminate\Http\Response;

class ProductSalesController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'product_sales_report', Sale::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        $data = [];

        $sale_details = SaleDetail::with('product', 'sale', 'sale.client', 'sale.warehouse')
            ->when(!$view_records, function ($query) {
                $query->whereHas('sale', function ($q) {
                    $q->where('user_id', '=', Auth::user()->id);
                });
            })
            ->whereBetween('date', [$request->from, $request->to])
            ->when($request->filled('client_id'), function ($query) use ($request) {
                $query->whereHas('sale.client', function ($q) use ($request) {
                    $q->where('client_id', '=', $request->client_id);
                });
            })
            ->when($request->filled('warehouse_id'), function ($query) use ($request) {
                $query->whereHas('sale.warehouse', function ($q) use ($request) {
                    $q->where('warehouse_id', '=', $request->warehouse_id);
                });
            })
            // Search With Multiple Param
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->whereHas('sale.client', function ($q) use ($request) {
                        $q->where('username', 'LIKE', "%{$request->search}%");
                    });
                })
                    ->orWhere(function ($query) use ($request) {
                        $query->whereHas('sale.warehouse', function ($q) use ($request) {
                            $q->where('name', 'LIKE', "%{$request->search}%");
                        });
                    })
                    ->orWhere(function ($query) use ($request) {
                        $query->whereHas('sale', function ($q) use ($request) {
                            $q->where('Ref', 'LIKE', "%{$request->search}%");
                        });
                    })
                    ->orWhere(function ($query) use ($request) {
                        $query->whereHas('product', function ($q) use ($request) {
                            $q->where('name', 'LIKE', "%{$request->search}%");
                        });
                    });
            })
            ->orderBy($request->SortField, $request->SortType)
            ->paginate($request->limit);



        $totalRows = $sale_details->total();

        foreach ($sale_details as $detail) {

            //check if detail has sale_unit_id Or Null
            if ($detail->sale_unit_id !== null) {
                $unit = Unit::where('id', $detail->sale_unit_id)->first();
            } else {
                $product_unit_sale_id = Product::with('unitSale')
                    ->where('id', $detail->product_id)
                    ->first();

                if ($product_unit_sale_id['unitSale']) {
                    $unit = Unit::where('id', $product_unit_sale_id['unitSale']->id)->first();
                } {
                    $unit = NULL;
                }
            }


            if ($detail->product_variant_id) {
                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $product_name = '[' . $productsVariants->name . ']' . $detail['product']['name'];

            } else {
                $product_name = $detail['product']['name'];
            }

            $item['date'] = $detail->date;
            $item['Ref'] = $detail['sale']->Ref;
            $item['client_name'] = $detail['sale']['client']->name;
            $item['warehouse_name'] = $detail['sale']['warehouse']->name;
            $item['quantity'] = $detail->quantity;
            $item['total'] = $detail->total;
            $item['product_name'] = $product_name;
            $item['unit_sale'] = $unit ? $unit->ShortName : '';

            $data[] = $item;
        }


        // get warehouses assigned to user
        $user = auth()->user();
        $warehouses = Warehouse::query()->when(!$user->is_all_warehouses, function ($query) use ($user) {
            $query->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        })->get(['id', 'name']);

        $customers = client::query()->get(['id', 'name']);

        return response()->json([
            'totalRows' => $totalRows,
            'sales' => $data,
            'customers' => $customers,
            'warehouses' => $warehouses,
        ], Response::HTTP_OK);
    }
}
