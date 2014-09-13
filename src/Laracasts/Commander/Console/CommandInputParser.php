<?php namespace Laracasts\Commander\Console;

class CommandInputParser {

    /**
     * @param $path
     * @param $properties
     * @return CommandInput
     */
    public function parse($path, $properties)
    {
        $segments = explode('\\', str_replace('/', '\\', $path));
        $name = array_pop($segments);
        $namespace = implode('\\', $segments);

        $properties = $this->parseProperties($properties);

        return new CommandInput($name, $namespace, $properties);
    }

    /**
     * @param $properties
     * @return array
     */
    private function parseProperties($properties)
    {
        return preg_split('/ ?, ?/', $properties, null, PREG_SPLIT_NO_EMPTY);
    }

}
