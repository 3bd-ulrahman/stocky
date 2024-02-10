<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'stock_report', Product::class);

        $user = auth()->user();
        $warehouses = Warehouse::query()->when(! $user->is_all_warehouses, function ($query) use($user) {
            $query->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        })->get(['id', 'name']);

        $products = Product::query()->when($request->warehouse_id, function ($query) use($request) {
                $query->whereHas('warehouses', fn($query) => $query->where('warehouse_id', $request->warehouse_id));
            })->with('unit', 'category', 'brand')
            ->withSum('warehouses as sum_product_warehouse_qantity', 'product_warehouse.qte')
            // ->where('type', '!=', 'is_service')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('products.name', 'LIKE', "%{$request->search}%")
                    ->orWhere('products.code', 'LIKE', "%{$request->search}%")
                    ->orWhere(function ($query) use ($request) {
                        $query->whereHas('category', function ($q) use ($request) {
                            $q->where('name', 'LIKE', "%{$request->search}%");
                        });
                    });
            })
            ->when($request->SortField == 'name',
                fn($query) => $query->orderByTranslation('name', $request->SortType),
                fn($query) => $query->orderBy($request->SortField, $request->SortType)
            )->paginate(10);

        $totalRows = $products->total();

        $data = $products->map(function ($product) {
            $item = [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'category' => $product->category->name,
            ];

            if ($product->type != 'is_service') {
                $current_stock = $product->sum_product_warehouse_qantity ?? 0;
                $item['quantity'] = $current_stock . ' ' . ($product->unit->ShortName ?? '');
            } else {
                $item['quantity'] = '---';
            }

            return $item;
        });

        return response()->json([
            'report' => $data,
            'totalRows' => $totalRows,
            'warehouses' => $warehouses,
        ]);
    }
}
