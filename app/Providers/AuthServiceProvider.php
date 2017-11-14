<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Validator;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }

    /**
     * 扩展的验证
     */
    private function extendValidator()
    {
        // 手机号
        Validator::extend(
            'mobile',
            function ($attribute, $value, $parameters) {
                return \App\Extensions\MyValidator::validateMobile($attribute, $value, $parameters);
            }
        );

        // 身份证号
        Validator::extend(
            'id_card',
            function ($attribute, $value, $parameters) {
                return \App\Extensions\MyValidator::validateIdCard($attribute, $value, $parameters);
            }
        );

        // 中文姓名
        Validator::extend(
            'chinese_name',
            function ($attribute, $value, $parameters) {
                return \App\Extensions\MyValidator::validateChineseName($attribute, $value, $parameters);
            }
        );
    }
}
