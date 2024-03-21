<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;

class TopProductController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Top_products', Product::class);

        $role = Auth::user()->roles()->first();
        $viewRecords = Role::findOrFail($role->id)->inRole('record_view');

        $products = SaleDetail::query()
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->join('product_translations', 'sale_details.product_id', '=', 'products.id')
            ->when(!$viewRecords, function ($query) {
                $query->where('sales.user_id', '=', Auth::user()->id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('products.name', 'LIKE', "%{$request->search}%")
                    ->orWhere('products.code', 'LIKE', "%{$request->search}%");
            })
            ->whereBetween('sale_details.date', [$request->from, $request->to])
            ->select(
                DB::raw('product_translations.name as name'),
                DB::raw('products.code as code'),
                DB::raw('count(*) as total_sales'),
                DB::raw('sum(total) as total'),
            )
            ->groupBy('product_translations.name')
            ->orderBy('total_sales', 'desc')
            ->paginate($request->limit);

        $totalRows = $products->total();


        return response()->json([
            'products' => $products,
            'totalRows' => $totalRows,
        ]);
    }
}
