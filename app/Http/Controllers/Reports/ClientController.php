<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Warehouse;

class ClientController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Reports_customers', Client::class);

        $clients = Client::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('code', 'LIKE', "%{$request->search}%")
                    ->orWhere('phone', 'LIKE', "%{$request->search}%");
            })
            ->when($request->warehouse_id, function ($query) {
                $query->whereHas('sales', function ($query) {
                    $query->where('warehouse_id', request()->warehouse_id);
                });
            })
            ->withCount('sales as total_sales')
            ->withSum('sales as total_amount', 'GrandTotal')
            ->withSum('sales as total_paid', 'paid_amount')
            ->withSum('saleReturns as total_amount_return', 'GrandTotal')
            ->withSum('saleReturns as total_paid_return', 'paid_amount')
            ->orderBy($request->SortField, $request->SortType)
            ->paginate($request->limit);

        $totalRows = $clients->total();

        $data = $clients->map(function ($client) {
            return [
                'total_sales' => $client->total_sales,
                'total_amount' => $client->total_amount,
                'total_paid' => $client->total_paid,
                'due' => $client->total_amount - $client->total_paid,
                'total_amount_return' => $client->total_amount_return,
                'total_paid_return' => $client->total_paid_return,
                'return_Due' => $client->total_amount_return - $client->total_paid_return,
                'name' => $client->name,
                'phone' => $client->phone,
                'code' => $client->code,
                'id' => $client->id,
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
            'warehouses' => $warehouses,
            'totalRows' => $totalRows,
        ]);
    }
}
