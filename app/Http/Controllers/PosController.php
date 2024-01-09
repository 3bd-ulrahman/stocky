<?php

namespace App\Http\Controllers;

use App\Models\UserWarehouse;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\PaymentSale;
use App\Models\Setting;
use App\Models\ProductVariant;
use App\Models\product_warehouse;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\SaleDetail;
use App\Models\Warehouse;
use App\Services\PaymentGatewayService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentGateway;

class PosController extends BaseController
{
    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        $clients = Client::query()->get(['id', 'name']);
        $settings = Setting::query()->with('Client')->first();

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::query()->get(['id', 'name']);

            if ($settings->warehouse_id) {
                if (Warehouse::where('id', $settings->warehouse_id)->first()) {
                    $defaultWarehouse = $settings->warehouse_id;
                } else {
                    $defaultWarehouse = '';
                }
            } else {
                $defaultWarehouse = '';
            }

        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();

            $warehouses = Warehouse::query()->whereHas('users', function ($query) use ($user_auth) {
                $query->where('user_id', $user_auth->id);
            })->get(['id', 'name']);

            if ($settings->warehouse_id) {
                if (Warehouse::where('id', $settings->warehouse_id)->whereIn('id', $warehouses_id)->first()) {
                    $defaultWarehouse = $settings->warehouse_id;
                } else {
                    $defaultWarehouse = '';
                }
            } else {
                $defaultWarehouse = '';
            }
        }

        if ($settings->client_id) {
            if (Client::where('id', $settings->client_id)->first()) {
                $defaultClient = $settings->client_id;
                $default_client_name = $settings['Client']->name;
            } else {
                $defaultClient = '';
                $default_client_name = '';
            }
        } else {
            $defaultClient = '';
            $default_client_name = '';
        }

        $categories = Category::query()->get();
        $brands = Brand::query()->get();

        $STRIPE_KEY = config('payment.STRIPE_KEY');
        $CHECKOUT_PUBLIC_KEY = config('payment.CHECKOUT_PUBLIC_KEY');
        $paymentGateway = PaymentGateway::query()->where('is_active', true)->pluck('name')->first();

        return response()->json([
            'STRIPE_KEY' => $STRIPE_KEY,
            'CHECKOUT_PUBLIC_KEY' => $CHECKOUT_PUBLIC_KEY,
            'paymentGateway' => $paymentGateway,
            'brands' => $brands,
            'defaultWarehouse' => $defaultWarehouse,
            'defaultClient' => $defaultClient,
            'default_client_name' => $default_client_name,
            'clients' => $clients,
            'warehouses' => $warehouses,
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);

        request()->validate([
            'client_id' => 'required',
            'warehouse_id' => 'required',
            'payment.amount' => 'required',
            'amount' => ['required', 'gt:0']
        ]);

        $item = DB::transaction(function () use ($request) {
            $role = Auth::user()->roles()->first();
            $view_records = Role::findOrFail($role->id)->inRole('record_view');

            $order = Sale::query()->create([
                'is_pos' => 1,
                'date' => Carbon::now(),
                'Ref' => getNumberOrder(),
                'client_id' => $request->client_id,
                'warehouse_id' => $request->warehouse_id,
                'tax_rate' => $request->tax_rate,
                'TaxNet' => $request->TaxNet,
                'discount' => $request->discount,
                'shipping' => $request->shipping,
                'GrandTotal' => $request->GrandTotal,
                'notes' => $request->notes,
                'statut' => 'completed',
                'payment_statut' => 'unpaid',
                'user_id' => Auth::user()->id,
            ]);

            $data = $request->details;
            foreach ($data as $key => $value) {

                $unit = Unit::where('id', $value['sale_unit_id'])->first();
                $orderDetails[] = [
                    'date' => Carbon::now(),
                    'sale_id' => $order->id,
                    'sale_unit_id' => $value['sale_unit_id'],
                    'quantity' => $value['quantity'],
                    'product_id' => $value['product_id'],
                    'product_variant_id' => $value['product_variant_id'],
                    'total' => $value['subtotal'],
                    'price' => $value['Unit_price'],
                    'TaxNet' => $value['tax_percent'],
                    'tax_method' => $value['tax_method'],
                    'discount' => $value['discount'],
                    'discount_method' => $value['discount_Method'],
                    'imei_number' => $value['imei_number'],
                ];

                $productWarehouse = product_warehouse::where('warehouse_id', $order->warehouse_id)
                    ->where('product_id', $value['product_id'])
                    ->when($value['product_variant_id'] !== null, function ($query) use($value) {
                        $query->where('product_variant_id', $value['product_variant_id']);
                    })
                    ->first();

                if ($unit && $productWarehouse) {
                    if ($unit->operator == '/') {
                        $productWarehouse->qte -= $value['quantity'] / $unit->operator_value;
                    } else {
                        $productWarehouse->qte -= $value['quantity'] * $unit->operator_value;
                    }
                    $productWarehouse->save();
                }
            }

            SaleDetail::insert($orderDetails);

            $sale = Sale::findOrFail($order->id);
            // Check If User Has Permission view All Records
            if (!$view_records) {
                // Check If User->id === sale->id
                $this->authorizeForUser($request->user('api'), 'check_record', $sale);
            }

            try {
                $total_paid = $sale->paid_amount + $request['amount'];
                $due = $sale->GrandTotal - $total_paid;

                if ($due === 0.0 || $due < 0.0) {
                    $payment_statut = 'paid';
                } else if ($due != $sale->GrandTotal) {
                    $payment_statut = 'partial';
                } else if ($due == $sale->GrandTotal) {
                    $payment_statut = 'unpaid';
                }


                if ($request->payment['Reglement'] == 'credit card') {

                    $paymentGateway = PaymentGateway::query()->where('is_active', true)->pluck('name')->first();

                    if ($paymentGateway === 'stripe') {
                        (new PaymentGatewayService)->stripe()->pay($request);
                    }

                    if ($paymentGateway === 'checkout') {
                        (new PaymentGatewayService)->checkout()->pay($request);
                    }


                    PaymentSale::query()->create([
                        'sale_id' => $order->id,
                        'Ref' => getNumberOrder(),
                        'date' => Carbon::now(),
                        'Reglement' => $request->payment['Reglement'],
                        'montant' => $request['amount'],
                        'change' => $request['change'],
                        'notes' => $request->payment['notes'],
                        'user_id' => Auth::user()->id
                    ]);

                    $sale->update([
                        'paid_amount' => $total_paid,
                        'payment_statut' => $payment_statut,
                    ]);

                    // Paying Method Cash
                } else {

                    PaymentSale::create([
                        'sale_id' => $order->id,
                        'Ref' => getNumberOrder(),
                        'date' => Carbon::now(),
                        'Reglement' => $request->payment['Reglement'],
                        'montant' => $request['amount'],
                        'change' => $request['change'],
                        'notes' => $request->payment['notes'],
                        'user_id' => Auth::user()->id,
                    ]);

                    $sale->update([
                        'paid_amount' => $total_paid,
                        'payment_statut' => $payment_statut,
                    ]);
                }


            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

            return $order->id;

        }, 10);

        return response()->json(['success' => true, 'id' => $item]);
    }

    //------------ Get Products--------------\\
    public function GetProductsByParametre(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Sales_pos', Sale::class);
        // How many items do you want to display.
        $perPage = 8;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $data = array();

        $product_warehouse_data = product_warehouse::where('warehouse_id', $request->warehouse_id)
            ->with('product', 'product.unitSale')
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($request) {
                return $query->whereHas('product', function ($q) use ($request) {
                    $q->where('not_selling', '=', 0);
                })
                    ->where(function ($query) use ($request) {
                        if ($request->stock == '1' && $request->product_service == '1') {
                            return $query->where('qte', '>', 0)->orWhere('manage_stock', false);

                        } elseif ($request->stock == '1' && $request->product_service == '0') {
                            return $query->where('qte', '>', 0)->orWhere('manage_stock', true);

                        } else {
                            return $query->where('manage_stock', true);
                        }
                    });
            })

            // Filter
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('category_id'), function ($query) use ($request) {
                    return $query->whereHas('product', function ($q) use ($request) {
                        $q->where('category_id', '=', $request->category_id);
                    });
                });
            })
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('brand_id'), function ($query) use ($request) {
                    return $query->whereHas('product', function ($q) use ($request) {
                        $q->where('brand_id', '=', $request->brand_id);
                    });
                });
            });

        $totalRows = $product_warehouse_data->count();

        $product_warehouse_data = $product_warehouse_data
            ->offset($offSet)
            ->limit(8)
            ->get();

        foreach ($product_warehouse_data as $product_warehouse) {
            if ($product_warehouse->product_variant_id) {
                $productsVariants = ProductVariant::where('product_id', $product_warehouse->product_id)
                    ->where('id', $product_warehouse->product_variant_id)
                    ->where('deleted_at', null)
                    ->first();

                $item['product_variant_id'] = $product_warehouse->product_variant_id;
                $item['Variant'] = '[' . $productsVariants->name . ']' . $product_warehouse['product']->name;
                $item['name'] = '[' . $productsVariants->name . ']' . $product_warehouse['product']->name;

                $item['code'] = $productsVariants->code;
                $item['barcode'] = $productsVariants->code;

                $product_price = $product_warehouse['productVariant']->price;

            } else {
                $item['product_variant_id'] = null;
                $item['Variant'] = null;
                $item['code'] = $product_warehouse['product']->code;
                $item['name'] = $product_warehouse['product']->name;
                $item['barcode'] = $product_warehouse['product']->code;

                $product_price = $product_warehouse['product']->price;

            }
            $item['id'] = $product_warehouse->product_id;
            $firstimage = explode(',', $product_warehouse['product']->image);
            $item['image'] = $firstimage[0];

            if ($product_warehouse['product']['unitSale']) {

                if ($product_warehouse['product']['unitSale']->operator == '/') {
                    $item['qte_sale'] = $product_warehouse->qte * $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_price / $product_warehouse['product']['unitSale']->operator_value;

                } else {
                    $item['qte_sale'] = $product_warehouse->qte / $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_price * $product_warehouse['product']['unitSale']->operator_value;

                }

            } else {
                $item['qte_sale'] = $product_warehouse['product']->type != 'is_service' ? $product_warehouse->qte : '---';
                $price = $product_price;
            }

            $item['unitSale'] = $product_warehouse['product']['unitSale'] ? $product_warehouse['product']['unitSale']->ShortName : '';
            $item['qte'] = $product_warehouse['product']->type != 'is_service' ? $product_warehouse->qte : '---';
            $item['product_type'] = $product_warehouse['product']->type;

            if ($product_warehouse['product']->TaxNet !== 0.0) {

                //Exclusive
                if ($product_warehouse['product']->tax_method == '1') {
                    $tax_price = $price * $product_warehouse['product']->TaxNet / 100;

                    $item['Net_price'] = $price + $tax_price;

                    // Inxclusive
                } else {
                    $item['Net_price'] = $price;
                }
            } else {
                $item['Net_price'] = $price;
            }

            $data[] = $item;
        }

        return response()->json([
            'products' => $data,
            'totalRows' => $totalRows,
        ]);
    }
}
