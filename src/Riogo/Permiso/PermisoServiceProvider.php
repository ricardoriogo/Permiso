<?php namespace Riogo\Permiso;

use Illuminate\Support\ServiceProvider;
use Riogo\Permiso\Commands\MigrationCommand;

class PermisoServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__.'/../../config/permiso.php' => config_path('permiso.php'),
         ]);


		\Auth::extend('permiso', function ($app, $name, array $config) {
			$provider = $app['auth']->createUserProvider($config['provider'] ?? null);

			$guard = new PermisoGuard($name, $provider, $app['session.store'], $app['request']);

			$guard->setCookieJar($app['cookie']);
			$guard->setDispatcher($app['events']);
			$guard->setRequest($app->refresh('request', $guard, 'setRequest'));

			return $guard;
		});

		$this->commands('command.permiso.migration');
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		// Change bindshared to singleton
		$this->app->singleton('command.permiso.migration', function ($app) {
			return new MigrationCommand();
		});

		$this->mergeConfigFrom(
			__DIR__.'/../../config/permiso.php', 'permiso'
        );
	}

	/**
      * Get the services provided.
      *
      * @return array
      */
     public function provides()
     {
         return array(
             'command.permiso.migration'
         );
     }
}
