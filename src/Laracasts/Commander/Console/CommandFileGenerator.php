<?php namespace Laracasts\Commander\Console;

use Illuminate\Filesystem\Filesystem;
use Mustache_Engine;

class CommandFileGenerator {

    /**
     * @var Filesystem
     */
    protected $file;

    /**
     * @var Mustache_Engine
     */
    protected $mustache;

    /**
     * @var CommandParametersParser
     */
    protected $parser;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $file
     * @param Mustache_Engine $mustache
     * @param CommandParametersParser $parser
     * @return CommandFileGenerator
     */
    public function __construct(Filesystem $file, Mustache_Engine $mustache, CommandParametersParser $parser)
    {
        $this->file = $file;
        $this->mustache = $mustache;
        $this->parser = $parser;
    }

    /**
     * @param $classPath
     * @param $stub
     * @param $base
     * @param $properties
     */
    public function make($classPath, $stub, $base, $properties)
    {
        // We'll first grab the template to use.
        $stub = $this->file->get($stub);

        // And then parse the command input into something we can use.
        $templateVars = $this->parser->parse($classPath, $properties);

        // And then we'll render the template using this data.
        $stub = $this->mustache->render($stub, $templateVars);

        // And finally write the file to the disk.
        return $this->write("{$classPath}.php", $stub, $base);
    }

    /**
     * Write the new file to disk.
     *
     * @param $path
     * @param $stub
     * @param $base
     */
    protected function write($path, $stub, $base)
    {
        $path = $base.'/'.$path;

        $this->file->put($path, $stub);
    }

} 