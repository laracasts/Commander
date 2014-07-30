<?php namespace Laracasts\Commander\Console;

class CommandParametersParser {

    /**
     * Parse the command input.
     *
     * @param $path
     * @param $properties
     * @return array
     */
    public function parse($path, $properties)
    {
        $segments = explode('/', $path);

        $properties = $this->parseProperties($properties);
        $name = array_pop($segments);
        $namespace = implode('\\', $segments);
        $arguments = $this->parseArgumentsFromProperties($properties);

        return compact('name', 'namespace', 'arguments', 'properties');
    }

    /**
     * Convert the string of properties into an array.
     *
     * @param $properties
     * @return mixed
     */
    protected function parseProperties($properties)
    {
        $properties = preg_split('/ ?, ?/', $properties, null, PREG_SPLIT_NO_EMPTY);

        return $properties ?: null;
    }

    /**
     * Turn the string of properties into an
     * argument list (for the constructor).
     *
     * @param $properties
     * @return mixed
     */
    protected function parseArgumentsFromProperties($properties)
    {
        if ( ! $properties) return null;

        return '$' . implode(', $', $properties);
    }

}