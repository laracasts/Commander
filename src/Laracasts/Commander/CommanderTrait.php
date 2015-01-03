<?php namespace Laracasts\Commander;

use ReflectionClass;
use InvalidArgumentException;
use Input, App;

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
        $class = new ReflectionClass($command);

        if(!$class->getConstructor())
        {
            return $this->mapInputToCommandProperties($class, $input);
        }
        else
        {
            return $this->mapInputToCommandConstructor($class, $input);
        }
    }

    /**
     * @param ReflectionClass $class
     * @param $input
     * @return object
     * @throws \InvalidArgumentException
     */
    protected function mapInputToCommandConstructor(ReflectionClass $class, array $input)
    {
        $dependencies = [];

        foreach ($class->getConstructor()->getParameters() as $parameter)
        {
            $name = $parameter->getName();

            if (array_key_exists($name, $input))
            {
                if($parameter->getClass() !== null)
                {
                    $className = $parameter->getClass()->getName();
                    $dependencies[] = new $className($input[$name]);
                }
                else
                {
                    $dependencies[] = $input[$name];
                }
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
     * @param ReflectionClass $class
     * @param $input
     * @throws \InvalidArgumentException
     * @return object
     */
    protected  function mapInputToCommandProperties(ReflectionClass $class, array $input)
    {
        $command = $class->newInstance();

        foreach($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property)
        {
            $name = $property->getName();

            if(array_key_exists($name, $input))
            {
                $command->{$name} = $input[$name];
            }
        }

        return $command;
    }

}
