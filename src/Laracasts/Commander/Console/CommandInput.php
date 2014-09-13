<?php namespace Laracasts\Commander\Console;

class CommandInput {

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $namespace;

    /**
     * @var array
     */
    public $properties = [];

    /**
     * @param $name
     * @param $namespace
     * @param $properties
     */
    public function __construct($name, $namespace, $properties)
    {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->properties = $properties;
    }

    /**
     * @return string
     */
    public function arguments()
    {
        return implode(', ', array_map(function($property)
        {
            return '$' . $property;
        }, $this->properties));
    }

}