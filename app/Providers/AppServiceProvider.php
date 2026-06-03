<?php

namespace App\Providers;

use App\Support\Utf8Sanitizer;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;
use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;

        $this->app->extend(ResponseFactoryContract::class, function ($factory, $app) use ($jsonOptions) {
            if (! $factory instanceof ResponseFactory) {
                return $factory;
            }

            return new class(
                $app->make(ViewFactoryContract::class),
                $app->make('redirect'),
                $jsonOptions
            ) extends ResponseFactory {
                public function __construct(
                    $view,
                    $redirector,
                    private readonly int $jsonOptions
                ) {
                    parent::__construct($view, $redirector);
                }

                public function json($data = [], $status = 200, array $headers = [], $options = 0)
                {
                    return new JsonResponse(
                        Utf8Sanitizer::sanitize($data),
                        $status,
                        $headers,
                        $this->jsonOptions | $options
                    );
                }
            };
        });
    }

    public function boot(): void
    {
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
    }
}
