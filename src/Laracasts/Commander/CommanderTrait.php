<?php namespace Laracasts\Commander;

use ReflectionClass;
use InvalidArgumentException;
use Input, App;

trait CommanderTrait {

    /**
     * Execute the command
     *
     * @param  string $command
     * @param  array $input
     * @param  array $decorators
     * @return mixed
     */
    public function execute($command, array $input = null, $decorators = [])
    {
        $input = $input ?: Input::all();

        $command = $this->mapInputToCommand($command, $input);

        $bus = $this->getCommandBus();

        // If any decorators are passed, we'll
        // filter through and register them
        // with the CommandBus, so that they
        // are executed first.
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
     * - Code courtesy of Taylor Otwell.
     *
     * @param  string $command
     * @param  array $input
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    protected function mapInputToCommand($command, array $input)
    {
        $class = new ReflectionClass($command);

        if (is_null($class->getConstructor())) {
            return $this->mapInputToCommandProperties($input, $class);
        } else {
            return $this->mapInputToCommandConstructor($input, $class);
        }
    }

    /**
     * Map an array of input to a command's properties via its constructor
     *
     * @param  array $input
     * @param  $class
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    protected function mapInputToCommandConstructor(array $input, $class)
    {
        $dependencies = [];

        foreach ($class->getConstructor()->getParameters() as $parameter)
        {
            $name = $parameter->getName();

            if (array_key_exists($name, $input))
            {
                $dependencies[] = $input[$name];
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

    /**
     * Map an array of input to a command's properties via its public properties
     *
     * @param  array $input
     * @param  $class
     *
     * @return mixed
     */
    protected function mapInputToCommandProperties(array $input, $class)
    {
        $instance = $class->newInstance();

        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $parameter)
        {
            $name = $parameter->getName();

            if (array_key_exists($name, $input))
            {
                $instance->$name = $input[$name];
            }
            else
            {
            	// if parameter has no default value
                if(is_null($instance->$name))
				{
					throw new InvalidArgumentException("Unable to map input to command: {$name}");
				}
            }
        }

        return $instance;
    }
}
