<?php namespace Riogo\Permiso;

use Illuminate\Support\ServiceProvider;

class PermisoServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		\Auth::extend('permiso', function() {
			$model = \Config::get('auth.model');
			$provider = new \Illuminate\Auth\EloquentUserProvider(\App::make('hash'), $model);
			return new PermisoGuard($provider, \App::make('session.store'));
		});
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

}
