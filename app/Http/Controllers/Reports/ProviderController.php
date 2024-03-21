<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Models\Provider;
use Illuminate\Http\Response;

class ProviderController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Reports_suppliers', Provider::class);

        $providers = Provider::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('code', 'LIKE', "%{$request->search}%")
                    ->orWhere('phone', 'LIKE', "%{$request->search}%");
            })
            ->when($request->warehouse_id, function ($query) {
                $query->whereHas('purchases', function ($query) {
                    $query->where('warehouse_id', request()->warehouse_id);
                });
            })
            ->withCount('purchases as total_purchase')
            ->withSum('purchases as total_amount', 'GrandTotal')
            ->withSum('purchases as total_paid', 'paid_amount')
            ->withSum('purchaseReturns as total_amount_return', 'GrandTotal')
            ->withSum('purchaseReturns as total_paid_return', 'paid_amount')
            ->orderBy($request->SortField, $request->SortType)
            ->paginate($request->limit);

        $totalRows = $providers->total();

        $data = $providers->map(function($provider) {
            return [
                'total_purchase' => $provider->total_purchase,
                'total_amount' => $provider->total_amount,
                'total_paid' => $provider->total_paid,
                'due' => $provider->total_amount - $provider->total_paid,
                'total_amount_return' => $provider->total_amount_return,
                'total_paid_return' => $provider->total_paid_return,
                'return_due' => $provider->total_amount_return - $provider->total_paid_return,
                'id' => $provider->id,
                'name' => $provider->name,
                'phone' => $provider->phone,
                'code' => $provider->code
            ];
        });

        $user = auth()->user();
        $warehouses = Warehouse::query()->when(!$user->is_all_warehouses, function ($query) use ($user) {
            $query->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        })->get(['id', 'name']);

        return response()->json([
            'report' => $data,
            'totalRows' => $totalRows,
            'warehouses' => $warehouses
        ], Response::HTTP_OK);
    }
}
