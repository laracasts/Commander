<?php namespace Laracasts\Commander;

use Illuminate\Foundation\Application;

class ValidationCommandBus implements CommandBus {

    private $bus;
    private $app;
    private $commandTranslator;

    function __construct(CommandBus $bus, Application $app, CommandTranslator $commandTranslator)
    {
        $this->bus = $bus;
        $this->app = $app;
        $this->commandTranslator = $commandTranslator;
    }

    /**
     * Execute a command with validation.
     *
     * @param $command
     * @return mixed
     */
    public function execute($command)
    {
        // If a validator is "registered," we will
        // first trigger it, before moving forward.
        $this->validateCommand($command);

        // When we're done, we'll move up the stack
        // and handle the rest.
        return $this->bus->execute($command);
    }

    /**
     * If appropriate, validate command data.
     *
     * @param $command
     */
    protected function validateCommand($command)
    {
        $validator = $this->commandTranslator->toValidator($command);

        if (class_exists($validator))
        {
            $this->app->make($validator)->validate($command);
        }
    }

}
