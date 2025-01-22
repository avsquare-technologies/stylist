<?php

namespace Mehedi\Stylist;

use Mehedi\Stylist\Theme\Loader;
use Mehedi\Stylist\Theme\Stylist;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\AggregateServiceProvider;

class StylistServiceProvider extends AggregateServiceProvider
{
    /**
     * Registers the various bindings required by other packages.
     */
    public function register()
    {
        parent::register();

        $this->registerConfiguration();
        $this->registerStylist();
        $this->registerAliases();
        $this->registerCommands();
    }

    /**
     * Boot the package, in this case also discovering any themes required by stylist.
     */
    public function boot()
    {
        $this->bootThemes();
    }

    /**
     * Once the provided has booted, we can now look at configuration and see if there's
     * any paths defined to automatically load and register the required themes.
     */
    protected function bootThemes()
    {
        $stylist = $this->app['stylist'];
        $paths = $this->app['config']->get('stylist.themes.paths', []);

        foreach ($paths as $path) {
            $themePaths = $stylist->discover($path);
            $stylist->registerPaths($themePaths);
        }

        $theme = $this->app['config']->get('stylist.themes.activate', null);

        if (!is_null($theme)) {
            $stylist->activate($theme, true);
        }
    }

    /**
     * Sets up the object that will be used for theme registration calls.
     */
    protected function registerStylist()
    {
        $this->app->singleton('stylist', function ($app) {
            return new Stylist(new Loader(), $app);
        });
    }

    /**
     * Stylist class should be accessible from global scope for ease of use.
     */
    private function registerAliases()
    {
        AliasLoader::getInstance()->alias('Stylist', 'Mehedi\Stylist\Facades\StylistFacade');

        $this->app->alias('stylist', 'Mehedi\Stylist\Theme\Stylist');
    }

    /**
     * Register the commands available to the package.
     */
    private function registerCommands()
    {
        $this->commands(
            'Mehedi\Stylist\Console\PublishAssetsCommand'
        );
    }

    /**
     * Setup the configuration that can be used by stylist.
     */
    protected function registerConfiguration()
    {
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('stylist.php')
        ]);
    }

    /**
     * An array of classes that Stylist provides.
     *
     * @return array
     */
    public function provides()
    {
        return array_merge(parent::provides(), ['Stylist']);
    }
}
