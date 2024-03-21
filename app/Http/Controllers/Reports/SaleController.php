<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Http\Response;

class SaleController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Reports_sales', Sale::class);

        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');

        $sales = Sale::query()->select('sales.*')
            ->with(['facture', 'client', 'warehouse'])
            ->when(!$view_records, function ($query) {
                $query->where('user_id', '=', Auth::user()->id);
            })
            ->whereBetween('sales.date', [$request->from, $request->to])
            ->when($request->representative_id, fn($query) => $query->where('representative_id', $request->representative_id))
            ->when($request->Ref, fn($query) => $query->where('Ref', 'like', $request->Ref))
            ->when($request->statut, fn($query) => $query->where('statut', 'like', $request->statut))
            ->when($request->payment_statut, fn($query) => $query->where('payment_statut', 'like', $request->payment_statut))
            ->when($request->client_id, fn($query) => $query->where('client_id', $request->client_id))
            ->when($request->warehouse_id, fn($query) => $query->where('warehouse_id', $request->warehouse_id))
            // Search With Multiple Param
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('Ref', 'LIKE', "%{$request->search}%")
                    ->orWhere('statut', 'LIKE', "%{$request->search}%")
                    ->orWhere('GrandTotal', $request->search)
                    ->orWhere('payment_statut', 'like', "%{$request->search}%")
                    ->orWhere('shipping_status', 'like', "%{$request->search}%")
                    ->orWhere(function ($query) use ($request) {
                        return $query->whereHas('client', function ($q) use ($request) {
                            $q->where('name', 'LIKE', "%{$request->search}%");
                        });
                    })
                    ->orWhere(function ($query) use ($request) {
                        return $query->whereHas('warehouse', function ($q) use ($request) {
                            $q->where('name', 'LIKE', "%{$request->search}%");
                        });
                    });
            })->orderBy("sales.{$request->SortField}", $request->SortType)
            ->paginate($request->limit);

        $totalRows = $sales->count();

        $sales = $sales->map(function ($Sale) {
            return [
                'id' => $Sale['id'],
                'date' => $Sale['date'],
                'Ref' => $Sale['Ref'],
                'statut' => $Sale['statut'],
                'discount' => $Sale['discount'],
                'shipping' => $Sale['shipping'],
                'warehouse_name' => $Sale['warehouse']['name'],
                'client_name' => $Sale['client']['name'],
                'client_email' => $Sale['client']['email'],
                'client_tele' => $Sale['client']['phone'],
                'client_code' => $Sale['client']['code'],
                'client_adr' => $Sale['client']['adresse'],
                'GrandTotal' => $Sale['GrandTotal'],
                'paid_amount' => $Sale['paid_amount'],
                'due' => $Sale['GrandTotal'] - $Sale['paid_amount'],
                'payment_status' => $Sale['payment_statut']
            ];
        });

        $customers = client::query()->get(['id', 'name']);

        //get warehouses assigned to user
        $user = auth()->user();
        $warehouses = Warehouse::query()->when(!$user->is_all_warehouses, function ($query) use ($user) {
            $query->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        })->get(['id', 'name']);

        $representatives = User::query()->where('is_representative', true)->get();

        return response()->json([
            'totalRows' => $totalRows,
            'sales' => $sales,
            'customers' => $customers,
            'warehouses' => $warehouses,
            'representatives' => $representatives
        ], Response::HTTP_OK);
    }
}
