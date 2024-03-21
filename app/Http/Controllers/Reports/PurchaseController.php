<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\Models\Purchase;
use App\Models\Warehouse;
use App\Models\Provider;

class PurchaseController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'ReportPurchases', Purchase::class);

        $role = Auth::user()->roles()->first();
        $viewRecords = Role::findOrFail($role->id)->inRole('record_view');

        $purchases = Purchase::query()->select('purchases.*')
            ->when(!$viewRecords, fn($query) => $query->where('user_id', Auth::user()->id))
            ->with('facture', 'provider', 'warehouse')
            ->join('providers', 'purchases.provider_id', '=', 'providers.id')
            ->whereBetween('purchases.date', array($request->from, $request->to))
            ->when($request->Ref, fn($query) => $query->where('Ref', 'like', "%{$request->Ref}%"))
            ->when($request->statut, fn($query) => $query->where('statut', $request->statut))
            ->when($request->provider_id, fn($query) => $query->where('provider_id', $request->provider_id))
            ->when($request->payment_statut, fn($query) => $query->where('payment_statut', $request->payment_statut))
            ->when($request->warehouse_id, fn($query) => $query->where('warehouse_id', $request->warehouse_id))
            // Search With Multiple Param
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('Ref', 'LIKE', "%{$request->search}%")
                    ->orWhere('statut', 'LIKE', "%{$request->search}%")
                    ->orWhere('GrandTotal', $request->search)
                    ->orWhere('payment_statut', 'like', "%{$request->search}%")
                    ->orWhere(function ($query) use ($request) {
                        $query->whereHas('provider', function ($q) use ($request) {
                            $q->where('name', 'LIKE', "%{$request->search}%");
                        });
                    })
                    ->orWhere(function ($query) use ($request) {
                        $query->whereHas('warehouse', function ($q) use ($request) {
                            $q->where('name', 'LIKE', "%{$request->search}%");
                        });
                    });
            })
            ->orderBy('purchases.' . $request->SortField, $request->SortType)
            ->paginate();

            $totalRows = $purchases->total();

        $data = $purchases->map(function ($purchase) {
            return [
                'id' => $purchase->id,
                'date' => $purchase->date,
                'Ref' => $purchase->Ref,
                'warehouse_name' => $purchase['warehouse']->name,
                'discount' => $purchase->discount,
                'shipping' => $purchase->shipping,
                'statut' => $purchase->statut,
                'provider_name' => $purchase['provider']->name,
                'provider_email' => $purchase['provider']->email,
                'provider_tele' => $purchase['provider']->phone,
                'provider_code' => $purchase['provider']->code,
                'provider_adr' => $purchase['provider']->adresse,
                'GrandTotal' => $purchase['GrandTotal'],
                'paid_amount' => $purchase['paid_amount'],
                'due' => $purchase['GrandTotal'] - $purchase['paid_amount'],
                'payment_status' => $purchase['payment_statut'],
            ];
        });

        $suppliers = provider::query()->get(['id', 'name']);

        //get warehouses assigned to user
        $user = auth()->user();
        $warehouses = Warehouse::query()->when(!$user->is_all_warehouses, function ($query) use ($user) {
            $query->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        })->get(['id', 'name']);

        return response()->json([
            'totalRows' => $totalRows,
            'purchases' => $data,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
        ]);
    }
}
