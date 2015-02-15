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


		\Auth::extend('permiso', function() {
			$model = \Config::get('auth.model');
			$provider = new \Illuminate\Auth\EloquentUserProvider(\App::make('hash'), $model);
			return new PermisoGuard($provider, \App::make('session.store'));
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
		$this->app->bindShared('command.permiso.migration', function ($app) {
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
