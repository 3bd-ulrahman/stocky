<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'users_report', User::class);

        $users = User::query()->withCount('sales as total_sales')
            ->withCount('purchases as total_purchases')
            ->withCount('quotations as total_quotations')
            ->withCount('SaleReturns as total_return_sales')
            ->withCount('purchaseReturns as total_return_purchases')
            ->withCount('transfers as total_transfers')
            ->withCount('adjustments as total_adjustments')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('username', 'LIKE', "%{$request->search}%");
            })
            ->when($request->warehouse_id, function ($query) {
                $query->whereHas('warehouses', function ($query) {
                    $query->where('warehouse_id', request()->warehouse_id);
                });
            })
            ->orderBy($request->SortField, $request->SortType)
            ->paginate($request->limit);

        $totalRows = $users->total();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'total_sales' => $user->total_sales,
                'total_purchases' => $user->total_purchases,
                'total_quotations' => $user->total_quotations,
                'total_return_sales' => $user->total_return_sales,
                'total_return_purchases' => $user->total_return_purchases,
                'total_transfers' => $user->total_transfers,
                'total_adjustments' => $user->total_adjustments
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
        ]);
    }
}
