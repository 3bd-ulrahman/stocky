<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\PaymentPurchase;
use App\Models\PaymentPurchaseReturns;
use App\Models\PaymentSale;
use App\Models\PaymentSaleReturns;
use App\Models\product_warehouse;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReturn;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    //----------------- dashboard_data -----------------------\\

    public function dashboard_data(Request $request)
    {
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $array_warehouses_id = Warehouse::query()->pluck('id')->toArray();
            $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        } else {
            $array_warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::query()->whereIn('id', $array_warehouses_id)->get(['id', 'name']);
        }

        if (empty($request->warehouse_id)) {
            $warehouse_id = 0;
        } else {
            $warehouse_id = $request->warehouse_id;
        }



        $dataSales = $this->SalesChart($warehouse_id, $array_warehouses_id);
        $datapurchases = $this->PurchasesChart($warehouse_id, $array_warehouses_id);
        $Payment_chart = $this->Payment_chart($warehouse_id, $array_warehouses_id);
        $TopCustomers = $this->TopCustomers($warehouse_id, $array_warehouses_id);
        $Top_Products_Year = $this->Top_Products_Year($warehouse_id, $array_warehouses_id);
        $report_dashboard = $this->report_dashboard($warehouse_id, $array_warehouses_id);

        return response()->json([
            'warehouses' => $warehouses,
            'sales' => $dataSales,
            'purchases' => $datapurchases,
            'payments' => $Payment_chart,
            'customers' => $TopCustomers,
            'product_report' => $Top_Products_Year,
            'report_dashboard' => $report_dashboard,
        ]);

    }

    //----------------- Sales Chart js -----------------------\\

    public function SalesChart($warehouse_id, $array_warehouses_id)
    {
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        // Build an array of the dates we want to show, oldest first
        $dates = Collection::make()->when(request()->period, function ($collection) {
            CarbonPeriod::create(request()->period[0], request()->period[1])->forEach(function (Carbon $date) use($collection) {
                $collection->put($date->format('Y-m-d'), 0);
            });
            return $collection;
        }, function ($collection) {
            return $collection->put(Carbon::today()->format('Y-m-d'), 0);
        });

        $date_range = Carbon::today()->subDays(6);
        // Get the sales counts
        $sales = Sale::query()->when(request()->period, function ($query) {
                $query->whereBetween('date', [request()->period[0], request()->period[1]]);
            }, function ($query) {
                $query->where('date', Carbon::now()->today()->format('Y-m-d'));
            })
            ->when(!$view_records, function($query) {
                $query->where('user_id', '=', Auth::user()->id);
            })
            ->when($warehouse_id !== 0, function ($query) use($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id);
            }, function ($query) use($array_warehouses_id) {
                $query->whereIn('warehouse_id', $array_warehouses_id);
            })
            ->groupBy(DB::raw("DATE_FORMAT(date,'%Y-%m-%d')"))
            ->orderBy('date', 'asc')
            ->get([
                DB::raw(DB::raw("DATE_FORMAT(date,'%Y-%m-%d') as date")),
                DB::raw('SUM(GrandTotal) AS count'),
            ])
            ->pluck('count', 'date');

        // Merge the two collections;
        $dates = $dates->merge($sales);

        $data = [];
        $days = [];
        foreach ($dates as $key => $value) {
            $data[] = $value;
            $days[] = $key;
        }

        return response()->json([
            'data' => $data,
            'days' => $days
        ]);
    }

    //----------------- Purchases Chart -----------------------\\

    public function PurchasesChart($warehouse_id, $array_warehouses_id)
    {

        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        // Build an array of the dates we want to show, oldest first
        $dates = collect();
        foreach (range(-6, 0) as $i) {
            $date = Carbon::now()->addDays($i)->format('Y-m-d');
            $dates->put($date, 0);
        }

        $date_range = Carbon::today()->subDays(6);

        // Get the purchases counts
        $purchases = Purchase::where('date', '>=', $date_range)
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
            ->where(function ($query) use ($warehouse_id, $array_warehouses_id) {
                if ($warehouse_id !== 0) {
                    return $query->where('warehouse_id', $warehouse_id);
                } else {
                    return $query->whereIn('warehouse_id', $array_warehouses_id);
                }
            })
            ->groupBy(DB::raw("DATE_FORMAT(date,'%Y-%m-%d')"))
            ->orderBy('date', 'asc')
            ->get([
                DB::raw(DB::raw("DATE_FORMAT(date,'%Y-%m-%d') as date")),
                DB::raw('SUM(GrandTotal) AS count'),
            ])
            ->pluck('count', 'date');

        // Merge the two collections;
        $dates = $dates->merge($purchases);

        $data = [];
        $days = [];
        foreach ($dates as $key => $value) {
            $data[] = $value;
            $days[] = $key;
        }

        return response()->json(['data' => $data, 'days' => $days]);

    }

    //-------------------- Get Top 5 Customers -------------\\

    public function TopCustomers($warehouse_id, $array_warehouses_id)
    {
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        $data = Sale::whereBetween('date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        ])->where('sales.deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('sales.user_id', '=', Auth::user()->id);
                }
            })

            ->where(function ($query) use ($warehouse_id, $array_warehouses_id) {
                if ($warehouse_id !== 0) {
                    return $query->where('sales.warehouse_id', $warehouse_id);
                } else {
                    return $query->whereIn('sales.warehouse_id', $array_warehouses_id);
                }
            })

            ->join('clients', 'sales.client_id', '=', 'clients.id')
            ->select(DB::raw('clients.name'), DB::raw("count(*) as value"))
            ->groupBy('clients.name')
            ->orderBy('value', 'desc')
            ->take(5)
            ->get();

        return response()->json($data);
    }


    //-------------------- Get Top 5 Products This YEAR -------------\\

    public function Top_Products_Year($warehouse_id, $array_warehouses_id)
    {
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        $products = SaleDetail::query()->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->whereBetween('sale_details.date', [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear(),
            ])
            ->when(!$view_records, function ($query) {
                $query->where('sales.user_id', '=', Auth::user()->id);
            })
            ->when($warehouse_id !== 0, function ($query) use($warehouse_id) {
                $query->where('sales.warehouse_id', $warehouse_id);
            }, function ($query) use($array_warehouses_id) {
                $query->whereIn('sales.warehouse_id', $array_warehouses_id);
            })
            ->select(
                DB::raw('products.name as name'),
                DB::raw('count(*) as value'),
            )
            ->groupBy('products.name')
            ->orderBy('value', 'desc')
            ->take(5)
            ->get();

        return response()->json($products);
    }


    //-------------------- General Report dashboard -------------\\

    public function report_dashboard($warehouse_id, $array_warehouses_id)
    {
        $Role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($Role->id)->inRole('record_view');

        // top selling product this month
        $products = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->whereBetween('sale_details.date', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('sales.user_id', '=', Auth::user()->id);
                }
            })
            ->where(function ($query) use ($warehouse_id, $array_warehouses_id) {
                if ($warehouse_id !== 0) {
                    return $query->where('sales.warehouse_id', $warehouse_id);
                } else {
                    return $query->whereIn('sales.warehouse_id', $array_warehouses_id);
                }
            })
            ->select(
                DB::raw('products.name as name'),
                DB::raw('count(*) as total_sales'),
                DB::raw('sum(total) as total'),
            )
            ->groupBy('products.name')
            ->orderBy('total_sales', 'desc')
            ->take(5)
            ->get();

        // Stock Alerts
        $product_warehouse_data = product_warehouse::with('warehouse', 'product', 'productVariant')
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where('manage_stock', true)
            ->whereRaw('qte <= stock_alert')
            ->where('product_warehouse.deleted_at', null)
            ->where(function ($query) use ($warehouse_id, $array_warehouses_id) {
                if ($warehouse_id !== 0) {
                    return $query->where('product_warehouse.warehouse_id', $warehouse_id);
                } else {
                    return $query->whereIn('product_warehouse.warehouse_id', $array_warehouses_id);
                }
            })

            ->take('5')->get();

        $stock_alert = [];
        if ($product_warehouse_data->isNotEmpty()) {

            foreach ($product_warehouse_data as $product_warehouse) {
                if ($product_warehouse->qte <= $product_warehouse['product']->stock_alert) {
                    if ($product_warehouse->product_variant_id !== null) {
                        $item['code'] = $product_warehouse['productVariant']->name . '-' . $product_warehouse['product']->code;
                    } else {
                        $item['code'] = $product_warehouse['product']->code;
                    }
                    $item['quantity'] = $product_warehouse->qte;
                    $item['name'] = $product_warehouse['product']->name;
                    $item['warehouse'] = $product_warehouse['warehouse']->name;
                    $item['stock_alert'] = $product_warehouse['product']->stock_alert;
                    $stock_alert[] = $item;
                }
            }

        }

        //---------------- sales

        $data['today_sales'] = Sale::query()
            ->when(request()->period, function ($query) {
                $query->whereBetween('date', [request()->period[0], request()->period[1]]);
            }, function ($query) {
                $query->where('date', Carbon::today());
            })
            ->when(!$view_records, function($query) {
                $query->where('user_id', '=', Auth::user()->id);
            })
            ->when($warehouse_id !== 0, function ($query) use($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id);
            }, function ($query) use($array_warehouses_id) {
                $query->whereIn('warehouse_id', $array_warehouses_id);
            })
            ->selectRaw('SUM(GrandTotal)  AS sum')
            ->first()->sum;

        $data['today_sales'] = number_format($data['today_sales'], 2, '.', ',');


        //--------------- return_sales

        $data['return_sales'] = SaleReturn::query()
            ->when(request()->period, function ($query) {
                $query->whereBetween('date', [request()->period[0], request()->period[1]]);
            }, function ($query) {
                $query->where('date', Carbon::today());
            })
            ->when(!$view_records, function($query) {
                $query->where('user_id', '=', Auth::user()->id);
            })
            ->when($warehouse_id !== 0, function ($query) use($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id);
            }, function ($query) use($array_warehouses_id) {
                $query->whereIn('warehouse_id', $array_warehouses_id);
            })
            ->selectRaw('SUM(GrandTotal)  AS sum')
            ->first()->sum;

        $data['return_sales'] = number_format($data['return_sales'], 2, '.', ',');

        //------------------- purchases

        $data['today_purchases'] = Purchase::query()->when(request()->period, function ($query) {
                $query->whereBetween('date', [request()->period[0], request()->period[1]]);
            }, function ($query) {
                $query->where('date', Carbon::today());
            })
            ->when(!$view_records, function($query) {
                $query->where('user_id', '=', Auth::user()->id);
            })
            ->when($warehouse_id !== 0, function ($query) use($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id);
            }, function ($query) use($array_warehouses_id) {
                $query->whereIn('warehouse_id', $array_warehouses_id);
            })
            ->selectRaw('SUM(GrandTotal)  AS sum')
            ->first()->sum;

        $data['today_purchases'] = number_format($data['today_purchases'], 2, '.', ',');

        //------------------------- return_purchases

        $data['return_purchases'] = PurchaseReturn::query()->when(request()->period, function ($query) {
                $query->whereBetween('date', [request()->period[0], request()->period[1]]);
            }, function ($query) {
                $query->where('date', Carbon::today());
            })
            ->when(!$view_records, function($query) {
                $query->where('user_id', '=', Auth::user()->id);
            })
            ->when($warehouse_id !== 0, function ($query) use($warehouse_id) {
                $query->where('warehouse_id', $warehouse_id);
            }, function ($query) use($array_warehouses_id) {
                $query->whereIn('warehouse_id', $array_warehouses_id);
            })
            ->selectRaw('SUM(GrandTotal)  AS sum')
            ->first()->sum;

        $data['return_purchases'] = number_format($data['return_purchases'], 2, '.', ',');

        $last_sales = [];

        //last sales
        $Sales = Sale::with('details', 'client', 'facture', 'warehouse')->where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
            ->where(function ($query) use ($warehouse_id, $array_warehouses_id) {
                if ($warehouse_id !== 0) {
                    return $query->where('warehouse_id', $warehouse_id);
                } else {
                    return $query->whereIn('warehouse_id', $array_warehouses_id);
                }
            })
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        foreach ($Sales as $Sale) {

            $item_sale['Ref'] = $Sale['Ref'];
            $item_sale['statut'] = $Sale['statut'];
            $item_sale['client_name'] = $Sale['client']['name'];
            $item_sale['warehouse_name'] = $Sale['warehouse']['name'];
            $item_sale['GrandTotal'] = $Sale['GrandTotal'];
            $item_sale['paid_amount'] = $Sale['paid_amount'];
            $item_sale['due'] = $Sale['GrandTotal'] - $Sale['paid_amount'];
            $item_sale['payment_status'] = $Sale['payment_statut'];

            $last_sales[] = $item_sale;
        }

        return response()->json([
            'products' => $products,
            'stock_alert' => $stock_alert,
            'report' => $data,
            'last_sales' => $last_sales,
        ]);

    }

    //----------------- Payment Chart js -----------------------\\

    public function Payment_chart($warehouse_id, $array_warehouses_id)
    {
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        // Build an array of the dates we want to show, oldest first
        $dates = collect();
        foreach (range(-6, 0) as $i) {
            $date = Carbon::now()->addDays($i)->format('Y-m-d');
            $dates->put($date, 0);
        }

        $date_range = Carbon::today()->subDays(6);
        // Get the sales counts
        $Payment_Sale = PaymentSale::with('sale')->where('date', '>=', $date_range)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
            ->where(function ($query) use ($warehouse_id, $array_warehouses_id) {
                if ($warehouse_id !== 0) {
                    return $query->whereHas('sale', function ($q) use ($array_warehouses_id, $warehouse_id) {
                        $q->where('warehouse_id', $warehouse_id);
                    });
                } else {
                    return $query->whereHas('sale', function ($q) use ($array_warehouses_id, $warehouse_id) {
                        $q->whereIn('warehouse_id', $array_warehouses_id);
                    });

                }
            })
            ->groupBy(DB::raw("DATE_FORMAT(date,'%Y-%m-%d')"))
            ->orderBy('date', 'asc')
            ->get([
                DB::raw(DB::raw("DATE_FORMAT(date,'%Y-%m-%d') as date")),
                DB::raw('SUM(montant) AS count'),
            ])
            ->pluck('count', 'date');

        $Payment_Sale_Returns = PaymentSaleReturns::with('SaleReturn')->where('date', '>=', $date_range)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
            ->where(function ($query) use ($warehouse_id, $array_warehouses_id) {
                if ($warehouse_id !== 0) {
                    return $query->whereHas('SaleReturn', function ($q) use ($array_warehouses_id, $warehouse_id) {
                        $q->where('warehouse_id', $warehouse_id);
                    });
                } else {
                    return $query->whereHas('SaleReturn', function ($q) use ($array_warehouses_id, $warehouse_id) {
                        $q->whereIn('warehouse_id', $array_warehouses_id);
                    });

                }
            })
            ->groupBy(DB::raw("DATE_FORMAT(date,'%Y-%m-%d')"))
            ->orderBy('date', 'asc')
            ->get([
                DB::raw(DB::raw("DATE_FORMAT(date,'%Y-%m-%d') as date")),
                DB::raw('SUM(montant) AS count'),
            ])
            ->pluck('count', 'date');

        $Payment_Purchases = PaymentPurchase::with('purchase')->where('date', '>=', $date_range)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
            ->where(function ($query) use ($warehouse_id, $array_warehouses_id) {
                if ($warehouse_id !== 0) {
                    return $query->whereHas('purchase', function ($q) use ($array_warehouses_id, $warehouse_id) {
                        $q->where('warehouse_id', $warehouse_id);
                    });
                } else {
                    return $query->whereHas('purchase', function ($q) use ($array_warehouses_id, $warehouse_id) {
                        $q->whereIn('warehouse_id', $array_warehouses_id);
                    });

                }
            })
            ->groupBy(DB::raw("DATE_FORMAT(date,'%Y-%m-%d')"))
            ->orderBy('date', 'asc')
            ->get([
                DB::raw(DB::raw("DATE_FORMAT(date,'%Y-%m-%d') as date")),
                DB::raw('SUM(montant) AS count'),
            ])
            ->pluck('count', 'date');

        $Payment_Purchase_Returns = PaymentPurchaseReturns::with('PurchaseReturn')->where('date', '>=', $date_range)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
            ->where(function ($query) use ($warehouse_id, $array_warehouses_id) {
                if ($warehouse_id !== 0) {
                    return $query->whereHas('PurchaseReturn', function ($q) use ($array_warehouses_id, $warehouse_id) {
                        $q->where('warehouse_id', $warehouse_id);
                    });
                } else {
                    return $query->whereHas('PurchaseReturn', function ($q) use ($array_warehouses_id, $warehouse_id) {
                        $q->whereIn('warehouse_id', $array_warehouses_id);
                    });

                }
            })
            ->groupBy(DB::raw("DATE_FORMAT(date,'%Y-%m-%d')"))
            ->orderBy('date', 'asc')
            ->get([
                DB::raw(DB::raw("DATE_FORMAT(date,'%Y-%m-%d') as date")),
                DB::raw('SUM(montant) AS count'),
            ])
            ->pluck('count', 'date');

        $Payment_Expense = Expense::where('date', '>=', $date_range)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
            ->where(function ($query) use ($warehouse_id, $array_warehouses_id) {
                if ($warehouse_id !== 0) {
                    return $query->where('warehouse_id', $warehouse_id);
                } else {
                    return $query->whereIn('warehouse_id', $array_warehouses_id);
                }
            })
            ->groupBy(DB::raw("DATE_FORMAT(date,'%Y-%m-%d')"))
            ->orderBy('date', 'asc')
            ->get([
                DB::raw(DB::raw("DATE_FORMAT(date,'%Y-%m-%d') as date")),
                DB::raw('SUM(amount) AS count'),
            ])
            ->pluck('count', 'date');

        $paymen_recieved = $this->array_merge_numeric_values($Payment_Sale, $Payment_Purchase_Returns);
        $payment_sent = $this->array_merge_numeric_values($Payment_Purchases, $Payment_Sale_Returns, $Payment_Expense);

        $dates_recieved = $dates->merge($paymen_recieved);
        $dates_sent = $dates->merge($payment_sent);

        $data_recieved = [];
        $data_sent = [];
        $days = [];
        foreach ($dates_recieved as $key => $value) {
            $data_recieved[] = $value;
            $days[] = $key;
        }

        foreach ($dates_sent as $key => $value) {
            $data_sent[] = $value;
        }

        return response()->json([
            'payment_sent' => $data_sent,
            'payment_received' => $data_recieved,
            'days' => $days,
        ]);

    }

    //----------------- array merge -----------------------\\

    public function array_merge_numeric_values()
    {
        $arrays = func_get_args();
        $merged = array();
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (!is_numeric($value)) {
                    continue;
                }
                if (!isset($merged[$key])) {
                    $merged[$key] = $value;
                } else {
                    $merged[$key] += $value;
                }
            }
        }
        return $merged;
    }
}
