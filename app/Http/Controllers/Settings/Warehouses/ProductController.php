<?php

namespace App\Http\Controllers\Settings\Warehouses;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\product_warehouse;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function index($warehouse, Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Product::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;

        // Filter fields With Params to retrieve
        $data = [];

        $products = Product::query()->whereHas('warehouses', function ($query) use($warehouse) {
                $query->where('warehouse_id', $warehouse);
            })
            ->with('unit', 'category', 'brand')
            ->when($request->name, fn($query) => $qyery->where('name', 'like', $request->name))
            ->when($request->category_id, fn($query) => $qyery->where('category_id', $request->category_id))
            ->when($request->brand_id, fn($query) => $qyery->where('brand_id', $request->brand_id))
            ->when($request->code, fn($query) => $qyery->where('code', 'like', $request->code))
            // Search With Multiple Param
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('product_translations.name', 'LIKE', "%{$request->search}%")
                    ->orWhere('products.code', 'LIKE', "%{$request->search}%")
                    ->orWhere(function ($query) use ($request) {
                        $query->whereHas('category', function ($q) use ($request) {
                            $q->where('name', 'LIKE', "%{$request->search}%");
                        });
                    })
                    ->orWhere(function ($query) use ($request) {
                        $query->whereHas('brand', function ($q) use ($request) {
                            $q->where('name', 'LIKE', "%{$request->search}%");
                        });
                    });
            });


        $totalRows = $products->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $products = $products->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($products as $product) {
            $item['id'] = $product->id;
            $item['code'] = $product->code;
            $item['name'] = $product->name;
            $item['category'] = $product['category']->name;
            $item['brand']['name'] = $product['brand']->name;


            $firstimage = explode(',', $product->image);
            $item['image'] = $firstimage[0];

            if ($product->type->value == 'is_single') {
                $item['type'] = 'Single';
                $item['cost'] = number_format($product->cost, 2, '.', ',');
                $item['price'] = number_format($product->price, 2, '.', ',');
                $item['unit'] = $product['unit']->ShortName ?? '';

                $product_warehouse_total_qty = product_warehouse::query()->where('product_id', $product->id)
                    ->sum('qte');

                $item['quantity'] = $product_warehouse_total_qty . ' ' . ($product['unit']->ShortName ?? '');

            } elseif ($product->type->value == 'is_variant') {
                $item['type'] = 'Variable';
                $product_variant_data = ProductVariant::query()->where('product_id', $product->id)
                    ->get();

                $item['cost'] = '';
                $item['price'] = '';
                $item['unit'] = $product['unit']->ShortName;

                foreach ($product_variant_data as $product_variant) {
                    $item['cost'] .= number_format($product_variant->cost, 2, '.', ',');
                    $item['cost'] .= '<br>';
                    $item['price'] .= number_format($product_variant->price, 2, '.', ',');
                    $item['price'] .= '<br>';
                }

                $product_warehouse_total_qty = product_warehouse::query()->where('product_id', $product->id)
                    ->sum('qte');

                $item['quantity'] = $product_warehouse_total_qty . ' ' . $product['unit']->ShortName;

            } else {
                $item['type'] = 'Service';
                $item['cost'] = '----';
                $item['quantity'] = '----';
                $item['unit'] = '----';

                $item['price'] = number_format($product->price, 2, '.', ',');
            }


            $data[] = $item;
        }

        $user = auth()->user();
        $warehouses = Warehouse::query()->when(! $user->is_all_warehouses, function ($query) use($user) {
            $query->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        })->get(['id', 'name']);

        $categories = Category::query()->get();
        $brands = Brand::query()->get();

        return response()->json([
            'warehouses' => $warehouses,
            'categories' => $categories,
            'brands' => $brands,
            'products' => $data,
            'totalRows' => $totalRows,
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function destroy(Warehouse $warehouse, $product)
    {
        $products = explode(',', $product);
        $warehouse->products()->detach($products);

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
