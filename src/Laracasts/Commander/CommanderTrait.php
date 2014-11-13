<?php namespace Laracasts\Commander;

use ReflectionClass;
use InvalidArgumentException;
use Input, App, Str;

trait CommanderTrait {

    /**
     * Execute the command.
     *
     * @param  string $command
     * @param  array $input
     * @param  array $decorators
     * @return mixed
     */
    protected function execute($command, array $input = null, $decorators = [])
    {
        $input = $input ?: Input::all();

        $command = $this->mapInputToCommand($command, $input);

        $bus = $this->getCommandBus();

        // If any decorators are passed, we'll filter through and register them
        // with the CommandBus, so that they are executed first.
        foreach ($decorators as $decorator)
        {
            $bus->decorate($decorator);
        }

        return $bus->execute($command);
    }

    /**
     * Fetch the command bus
     *
     * @return mixed
     */
    public function getCommandBus()
    {
        return App::make('Laracasts\Commander\CommandBus');
    }

    /**
     * Map an array of input to a command's properties.
     *
     * @param  string $command
     * @param  array $input
     * @throws InvalidArgumentException
     * @author Taylor Otwell
     *
     * @return mixed
     */
    protected function mapInputToCommand($command, array $input)
    {
        $dependencies = [];

        $class = new ReflectionClass($command);

        foreach ($class->getConstructor()->getParameters() as $parameter)
        {
            $name = $parameter->getName();

            if (array_key_exists($name, $input))
            {
                $dependencies[] = $input[$name];
            }
            elseif (array_key_exists(Str::snake($name), $input))
            {
                $dependencies[] = $input[Str::snake($name)];
            }
            elseif ($parameter->isDefaultValueAvailable())
            {
                $dependencies[] = $parameter->getDefaultValue();
            }
            else
            {
                throw new InvalidArgumentException("Unable to map input to command: {$name}");
            }
        }

        return $class->newInstanceArgs($dependencies);
    }

}
