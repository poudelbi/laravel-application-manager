<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\PageRepositoryInterface;
use App\Interfaces\PageCategoryRepositoryInterface;
use App\Interfaces\MenuRepositoryInterface;
use App\Interfaces\ImageRepositoryInterface;
use App\Repositories\PageRepository;
use App\Repositories\PageCategoryRepository;
use App\Repositories\MenuRepository;
use App\Repositories\ImageRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            PageRepositoryInterface::class,
            PageRepository::class
        );

        $this->app->bind(
            PageCategoryRepositoryInterface::class,
            PageCategoryRepository::class
        );

        $this->app->bind(
            MenuRepositoryInterface::class,
            MenuRepository::class
        );

        $this->app->bind(
            ImageRepositoryInterface::class,
            ImageRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}