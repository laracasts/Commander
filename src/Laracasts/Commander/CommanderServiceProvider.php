<?php namespace Laracasts\Commander;

use Illuminate\Support\ServiceProvider;
use Laracasts\Commander\Console\CommandInputParser;

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

        $this->registerArtisanCommand();
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

    /**
     * Register the command translator binding
     */
    protected function registerCommandTranslator()
    {
        $this->app->bind('Laracasts\Commander\CommandTranslator', 'Laracasts\Commander\BasicCommandTranslator');
    }

    /**
     * Register the desired command bus implementation
     */
    protected function registerCommandBus()
    {
        $this->app->bindShared('Laracasts\Commander\CommandBus', function ($app)
        {
            $default = $app->make('Laracasts\Commander\DefaultCommandBus');
            $translator = $app->make('Laracasts\Commander\CommandTranslator');

            return new ValidationCommandBus($default, $app, $translator);
        });
    }

    /**
     * Register the Artisan command
     *
     * @return void
     */
    public function registerArtisanCommand()
    {
        $this->app->bindShared('commander.command.make', function($app)
        {
            return $app->make('Laracasts\Commander\Console\CommanderGenerateCommand');
        });

        $this->commands('commander.command.make');
    }

}
