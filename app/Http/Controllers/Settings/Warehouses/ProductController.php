<?php

namespace App\Http\Controllers\Settings\Warehouses;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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
        $pageStart = $request->get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;

        // Filter fields With Params to retrieve
        $data = [];

        $products = Product::query()->whereHas('warehouses', function ($query) use($warehouse) {
                $query->where('warehouse_id', $warehouse);
            })
            ->with(['unit', 'category', 'brand', 'productVariant', 'warehouses'])
            ->withSum('warehouses as sum_product_warehouse_qantity', 'product_warehouse.qte')
            ->when($request->name, fn($query) => $query->where('name', 'like', $request->name))
            ->when($request->category_id, fn($query) => $query->where('category_id', $request->category_id))
            ->when($request->brand_id, fn($query) => $query->where('brand_id', $request->brand_id))
            ->when($request->code, fn($query) => $query->where('code', 'like', $request->code))
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
            })->offset($offSet)
            ->limit($perPage)
            ->when($request->SortField == 'name',
                fn($query) => $query->orderByTranslation('name', $request->SortType),
                fn($query) => $query->orderBy($request->SortField, $request->SortType)
            )->get();

        $totalRows = $products->count();

        foreach ($products as $product) {
            $item['id'] = $product->id;
            $item['code'] = $product->code;
            $item['name'] = $product->translations[0]->name;
            $item['category'] = $product['category']->name;
            $item['brand']['name'] = $product['brand']->name;

            $firstimage = explode(',', $product->image);
            $item['image'] = $firstimage[0];

            if ($product->type->value == 'is_single') {
                $item['type'] = 'Single';
                $item['cost'] = number_format($product->cost, 2, '.', ',');
                $item['price'] = number_format($product->price, 2, '.', ',');
                $item['unit'] = $product['unit']->ShortName ?? '';

                $product_warehouse_total_qty = $product->sum_product_warehouse_qantity;

                $item['quantity'] = "{$product_warehouse_total_qty} {$item['unit']}";

            } elseif ($product->type->value == 'is_variant') {
                $item['type'] = 'Variable';

                $item['cost'] = '';
                $item['price'] = '';
                $item['unit'] = $product['unit']->ShortName;

                foreach ($product->productVariant as $product_variant) {
                    $item['cost']  .= number_format($product_variant->cost, 2, '.', ',');
                    $item['cost']  .= '<br>';
                    $item['price'] .= number_format($product_variant->price, 2, '.', ',');
                    $item['price'] .= '<br>';
                }

                $product_warehouse_total_qty = $product->sum_product_warehouse_qantity;

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

        $categories = Category::query()->get();
        $brands = Brand::query()->get();

        return response()->json([
            'categories' => $categories,
            'brands' => $brands,
            'products' => $data,
            'totalRows' => $totalRows,
        ], Response::HTTP_OK);
    }

    public function create($warehouse, Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Product::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = $request->get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;

        // Filter fields With Params to retrieve
        $data = [];

        $products = Product::query()->whereHas('warehouses', function ($query) use($warehouse) {
                $query->where('warehouse_id', '!=', $warehouse);
            })
            ->with(['unit', 'category', 'brand', 'productVariant', 'warehouses'])
            ->withSum('warehouses as sum_product_warehouse_qantity', 'product_warehouse.qte')
            ->when($request->name, fn($query) => $query->where('name', 'like', $request->name))
            ->when($request->category_id, fn($query) => $query->where('category_id', $request->category_id))
            ->when($request->brand_id, fn($query) => $query->where('brand_id', $request->brand_id))
            ->when($request->code, fn($query) => $query->where('code', 'like', $request->code))
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
            })->offset($offSet)
            ->limit($perPage)
            ->when($request->SortField == 'name',
                fn($query) => $query->orderByTranslation('name', $request->SortType),
                fn($query) => $query->orderBy($request->SortField, $request->SortType)
            )->get();

        $totalRows = $products->count();

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

                $product_warehouse_total_qty = $product->sum_product_warehouse_qantity;

                $item['quantity'] = "{$product_warehouse_total_qty} {$item['unit']}";

            } elseif ($product->type->value == 'is_variant') {
                $item['type'] = 'Variable';

                $item['cost'] = '';
                $item['price'] = '';
                $item['unit'] = $product['unit']->ShortName ?? '';

                foreach ($product->productVariant as $product_variant) {
                    $item['cost']  .= number_format($product_variant->cost, 2, '.', ',');
                    $item['cost']  .= '<br>';
                    $item['price'] .= number_format($product_variant->price, 2, '.', ',');
                    $item['price'] .= '<br>';
                }

                $product_warehouse_total_qty = $product->sum_product_warehouse_qantity;

                $item['quantity'] = $product_warehouse_total_qty . ' ' . $item['unit'];

            } else {
                $item['type'] = 'Service';
                $item['cost'] = '----';
                $item['quantity'] = '----';
                $item['unit'] = '----';

                $item['price'] = number_format($product->price, 2, '.', ',');
            }


            $data[] = $item;
        }

        $categories = Category::query()->get();
        $brands = Brand::query()->get();

        return response()->json([
            'categories' => $categories,
            'brands' => $brands,
            'products' => $data,
            'totalRows' => $totalRows,
        ], Response::HTTP_OK);
    }

    public function store(Warehouse $warehouse, Request $request)
    {
        $warehouse->products()->attach($request->products, ['qte' => 0]);

        return response()->json(status: Response::HTTP_CREATED);
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
