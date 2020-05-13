<?php

namespace jeremykenedy\Uuid;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class UuidServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('uuid', function ($attribute, $value, $parameters, $validator) {
            return Uuid::validate($value);
        });
    }
}
