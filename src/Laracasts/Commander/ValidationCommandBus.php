<?php namespace Laracasts\Commander;

use Illuminate\Foundation\Application;
use InvalidArgumentException;

class ValidationCommandBus implements CommandBus {

    /**
     * @var CommandBus
     */
    protected $bus;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var CommandTranslator
     */
    protected $commandTranslator;

    /**
     * List of optional decorators for command bus.
     *
     * @var array
     */
    protected $decorators = [];

    function __construct(CommandBus $bus, Application $app, CommandTranslator $commandTranslator)
    {
        $this->bus = $bus;
        $this->app = $app;
        $this->commandTranslator = $commandTranslator;
    }

    /**
     * Decorate the command bus with any executable actions.
     *
     * @param  string $className
     * @return mixed
     */
    public function decorate($className)
    {
        $this->decorators[] = $className;
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

        // Next, we'll execute any registered decorators.
        $this->executeDecorators($command);

        // And finally pass through to the handler class.
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

    /**
     * Execute all registered decorators
     *
     * @param  object $command
     * @return null
     */
    protected function executeDecorators($command)
    {
        foreach ($this->decorators as $className)
        {
            $instance = $this->app->make($className);

            if ( ! $instance instanceof CommandBus)
            {
                $message = 'The class to decorate must be an implementation of Laracasts\Commander\CommandBus';

                throw new InvalidArgumentException($message);
            }

            $instance->execute($command);
        }
    }

}
