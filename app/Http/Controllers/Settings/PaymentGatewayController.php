<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\Setting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentGatewayController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'payment_gateway', Setting::class);

        Artisan::call('config:cache');
        Artisan::call('config:clear');

        $paymentGateways = PaymentGateway::query()->get();

        $transformedGateways = collect();

        $paymentGateways->each(function ($gateway) use ($transformedGateways) {
            $name = $gateway->name;
            $keys = $gateway->keys;
            $isActive = $gateway->is_active;

            $transformedGateways->put($name, array_merge(
                (array) $keys, ['is_active' => $isActive]
            ));
        });

        $transformedGateways->toJson(JSON_PRETTY_PRINT);

        return response()->json([
            'gateway' => $transformedGateways
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'payment_gateway', Setting::class);

        if ($request->delete) {
            $this->setEnvironmentValue(array_fill_keys(array_keys($request->keys), ''));
        }

        DB::transaction(function () use ($request) {
            PaymentGateway::query()->update(['is_active' => false]);
            PaymentGateway::query()->updateOrCreate(['name' => $request->name], [
                'name' => $request->name,
                'keys' => $request->keys,
                'is_active' => 1
            ]);
        });

        $this->setEnvironmentValue($request->keys);

        Artisan::call('config:clear');
        Artisan::call('config:cache');

        return response()->json(['success' => true], Response::HTTP_OK);
    }

    private function setEnvironmentValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {

                $keyPosition = strpos($str, "$envKey=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                if (is_bool($keyPosition) && $keyPosition === false) {
                    // variable doesnot exist
                    $str .= "$envKey=$envValue";
                    $str .= "\r\n";
                } else {
                    // variable exist
                    $str = str_replace($oldLine, "$envKey=$envValue", $str);
                }
            }
        }

        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) {
            return false;
        }

        app()->loadEnvironmentFrom($envFile);

        return true;
    }
}
