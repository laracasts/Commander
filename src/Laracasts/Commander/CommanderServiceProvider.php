<?php namespace Laracasts\Commander;

use Illuminate\Support\ServiceProvider;

class CommanderServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->registerCommandTranslator();

        $this->registerCommandBus();
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['commander'];
	}

    private function registerCommandTranslator()
    {
        $this->app->bind('Laracasts\Commander\CommandTranslator', 'Laracasts\Commander\BasicCommandTranslator');
    }

    private function registerCommandBus()
    {
        $this->app->bindShared('Laracasts\Commander\CommandBus', function ()
        {
            return $this->app->make('Laracasts\Commander\ValidationCommandBus');
        });
    }

}
