<?php

use Illuminate\Support\Facades\DB;

if (! function_exists('fake') && class_exists(\Faker\Factory::class)) {
    /**
     * Get a faker instance.
     *
     * @param  string|null  $locale
     * @return \Faker\Generator
     */
    function fake($locale = null)
    {
        if (app()->bound('config')) {
            $locale ??= app('config')->get('app.faker_locale');
        }

        $locale ??= 'en_US';

        $abstract = \Faker\Generator::class.':'.$locale;

        if (! app()->bound($abstract)) {
            app()->singleton($abstract, fn () => \Faker\Factory::create($locale));
        }

        return app()->make($abstract);
    }
}

if (! function_exists('getNumberOrder')) {
    function getNumberOrder()
    {
        $last = DB::table('payment_sale_returns')->latest('id')->first();

        if ($last) {
            $item = $last->Ref;
            $nwMsg = explode("_", $item);
            $inMsg = $nwMsg[1] + 1;
            $code = $nwMsg[0] . '_' . $inMsg;
        } else {
            $code = 'INV/RT_1111';
        }

        return $code;
    }
}

if (! function_exists('setEnvValue')) {
    function setEnvValue($key, $value)
    {
        $envFilePath = base_path('.env');

        $contents = file_get_contents($envFilePath);

        // Update the environment variable dynamically
        $contents = preg_replace("/^$key=.*$/m", "$key='$value'", $contents);

        // Write the updated contents back to the .env file
        file_put_contents($envFilePath, $contents);
    }
}
