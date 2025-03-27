<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Brands;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        ProductCategory::class => CategoryPolicy::class,
        Brands::class => BrandPolicy::class,
        Products::class => ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
