<?php namespace Laracasts\Commander\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;
use Mustache_Engine;

class CommanderGenerateCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'commander:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new command and handler class.';

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
     * @return CommanderGenerateCommand
     */
    public function __construct(Filesystem $file, Mustache_Engine $mustache, CommandParametersParser $parser)
    {
        $this->file = $file;
        $this->mustache = $mustache;
        $this->parser = $parser;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $path = str_replace('\\', '/', $this->argument('path'));
        $properties = $this->option('properties');
        $base = $this->option('base');

        $this->generateCommandClass($path, $properties, $base);
        $this->generateHandlerClass($path, $properties, $base);
    }

    /**
     * @param $path
     * @param $properties
     * @param $base
     */
    public function generateCommandClass($path, $properties, $base)
    {
        $templateVars = $this->parser->parse($path, $properties);
        $stub = $this->render(__DIR__.'/stubs/command.stub', $templateVars);

        $this->write("{$path}.php", $stub, $base);
    }

    /**
     * @param $path
     * @param $properties
     * @param $base
     */
    public function generateHandlerClass($path, $properties, $base)
    {
        $templateVars = $this->parser->parse($path, $properties);
        $stub = $this->render(__DIR__.'/stubs/handler.stub', $templateVars);

        $this->write("{$path}Handler.php", $stub, $base);
    }

    /**
     * Compile the stub against the template vars.
     *
     * @param $stub
     * @param $templateVars
     * @return string
     */
    protected function render($stub, $templateVars)
    {
        $stub = $this->file->get($stub);

        return $this->mustache->render($stub, $templateVars);
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

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['path', InputArgument::REQUIRED, 'The class path for the command to generate.']
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['properties', null, InputOption::VALUE_OPTIONAL, 'A comma-separated list of properties for the command.', null],
            ['base', null, InputOption::VALUE_OPTIONAL, 'The path to where your domain root is located.', 'app']
        ];
    }

}
