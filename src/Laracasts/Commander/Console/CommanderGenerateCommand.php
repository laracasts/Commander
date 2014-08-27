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
     * @var CommandInputParser
     */
    protected $parser;

    /**
     * @var CommandGenerator
     */
    protected $generator;

    /**
     * Create a new command instance.
     *
     * @param CommandInputParser $parser
     * @param CommandGenerator $generator
     */
    public function __construct(CommandInputParser $parser, CommandGenerator $generator)
    {
        $this->parser = $parser;
        $this->generator = $generator;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $path = $this->argument('path');
        $properties = $this->option('properties');
        $base = $this->option('base');

        // Parse the command input.
        $commandInput = $this->parser->parse($path, $properties);
        $handlerInput = $this->parser->parse($path.'Handler', $properties);

        // Actually create the files with the correct boilerplate.
        $this->generator->make(
            $commandInput,
            __DIR__.'/stubs/command.stub',
            "{$base}/{$path}.php"
        );

        $this->generator->make(
            $handlerInput,
            __DIR__.'/stubs/handler.stub',
            "{$base}/{$path}Handler.php"
        );

        $this->info('All done! Your two classes have now been generated.');
    }

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
