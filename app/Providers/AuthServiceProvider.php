<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Brands;
use App\Models\Color;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\news;
use App\Models\NewsCategory;
use App\Models\Order;
use App\Models\Permission;
use App\Models\ProductCategory;
use App\Models\Products;
use App\Models\Review;
use App\Models\Role;
use App\Models\Size;
use App\Models\UserClient;
use App\Models\Voucher;
use App\Policies\AccountPolicy;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ColorPolicy;
use App\Policies\CommentPolicy;
use App\Policies\ContactPolicy;
use App\Policies\NewsCategoryPolicy;
use App\Policies\NewsPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\PermssionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\RolePolicy;
use App\Policies\SizePolicy;
use App\Policies\VoucherPolicy;
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
        Size::class => SizePolicy::class,
        Color::class => ColorPolicy::class,
        UserClient::class => AccountPolicy::class,
        Voucher::class => VoucherPolicy::class,
        news::class => NewsPolicy::class,
        NewsCategory::class => NewsCategoryPolicy::class,
        Review::class => ReviewPolicy::class,
        Comment::class => CommentPolicy::class,
        Order::class => OrderPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        Contact::class => ContactPolicy::class,
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
