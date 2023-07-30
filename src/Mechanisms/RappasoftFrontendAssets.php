<?php

namespace Rappasoft\LaravelLivewireTables\Mechanisms;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Livewire\Drawer\Utils;

class RappasoftFrontendAssets
{
    public bool $hasRenderedRappsoftScripts = false;

    public bool $hasRenderedRappsoftStyles = false;

    public $rappasoftScriptRoute;

    public $rappasoftStylesRoute;

    public array $rappasoftScriptTagAttributes = [];

    public function register(): void
    {
        app()->singleton($this::class);
    }

    public function boot()
    {
        app($this::class)->setRappaScriptRoute(function ($handle) {
            $scriptPath = '/livewire/rappasoft-laravel-livewire-tabsl2eqsbs.js';

            return Route::get($scriptPath, $handle);
        });

        Blade::directive('rappasoftScripts', [static::class, 'rappasoftScripts']);
        Blade::directive('rappasoftStyles', [static::class, 'rappasoftStyles']);
    }

    public function useRappasoftScriptTagAttributes($attributes): void
    {
        $this->rappasoftScriptTagAttributes = array_merge($this->rappasoftScriptTagAttributes, $attributes);
    }

    public function setRappaScriptRoute($callback): void
    {
        $route = $callback([self::class, 'returnJavaScriptAsFile']);

        $this->rappasoftScriptRoute = $route;
    }

    public function setStylesRoute($callback): void
    {
        $route = $callback([self::class, 'returnStylesAsFile']);

        $this->rappasoftStylesRoute = $route;
    }

    public static function rappasoftScripts($expression): string
    {
        return '{!! \Rappasoft\LaravelLivewireTables\Mechanisms\RappasoftFrontendAssets::scripts('.$expression.') !!}';
    }

    public static function rappasoftStyles($expression): string
    {
        return '{!! \Rappasoft\LaravelLivewireTables\Mechanisms\RappasoftFrontendAssets::styles('.$expression.') !!}';
    }

    public function returnJavaScriptAsFile()
    {
        return Utils::pretendResponseIsFile(__DIR__.'/../../resources/js/laravel-livewire-tables.js');
    }

    public function returnStylesAsFile()
    {
        return Utils::pretendResponseIsFile(__DIR__.'/../../resources/css/test.css');
    }

    public function maps()
    {
        return Utils::pretendResponseIsFile(__DIR__.'/../../../resources/js/laravel-livewire-tables.js.map');
    }

    public static function styles($options = []): array|string|null
    {
        app(static::class)->hasRenderedRappsoftStyles = true;

        $nonce = isset($options['nonce']) ? "nonce=\"{$options['nonce']}\"" : '';

        $html = <<<HTML
        <!-- Rappasoft Styles -->
        <style {$nonce}>
            div.testIncludeRappasoftFrontendAssets {
                background-color: #000 !important;
            }
        </style>
        HTML;

        return static::minify($html);
    }

    public static function scripts($options = []): string
    {
        app(static::class)->hasRenderedRappsoftScripts = true;

        $debug = config('app.debug');

        $scripts = static::js($options);

        // HTML Label.
        $html = $debug ? ['<!-- Rappasoft Scripts -->'] : [];

        $html[] = $scripts;

        return implode("\n", $html);
    }

    public static function js($options): string
    {
        // Use the default endpoint...
        $url = app(static::class)->rappasoftScriptRoute->uri;

        $url = rtrim($url, '/');

        $url = (string) str($url)->start('/');

        // Add the build manifest hash to it...

        $nonce = isset($options['nonce']) ? "nonce=\"{$options['nonce']}\"" : '';

        $extraAttributes = Utils::stringifyHtmlAttributes(
            app(static::class)->rappasoftScriptTagAttributes,
        );

        return <<<HTML
        <script  src="{$url}" async {$nonce} {$extraAttributes}></script>
        HTML;
    }

    protected static function minify($subject): array|string|null
    {
        return preg_replace('~(\v|\t|\s{2,})~m', '', $subject);
    }
}
