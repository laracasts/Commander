<?php

namespace spec\Laracasts\Commander\Console;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Illuminate\Filesystem\Filesystem;
use Mustache_Engine;
use Laracasts\Commander\Console\CommandParametersParser;

class CommanderGenerateCommandSpec extends ObjectBehavior {

	function let(Filesystem $file, Mustache_Engine $mustache, CommandParametersParser $parser)
	{
		$this->beConstructedWith($file, $mustache, $parser);
	}

	function it_creates_a_command_class(Filesystem $file, CommandParametersParser $parser, Mustache_Engine $mustache )
	{
		$input = [
			'path' => 'SomeCommand',
			'--properties' => 'first, last',
			'base' => 'foo'
		];

		$stub = 'class SomeCommand {}';

		$file->get(Argument::any())->willReturn('template');
		$parser->parse($input['path'], $input['--properties'])->willReturn([]);
		$mustache->render('template', [])->shouldBeCalled()->willReturn($stub);

		$file->put('foo/'.$input['path'].'.php', $stub)->shouldBeCalled();

		$this->generateCommandClass($input['path'], $input['--properties'], $input['base']);
	}

	function it_creates_a_handler_class(Filesystem $file, CommandParametersParser $parser, Mustache_Engine $mustache )
	{
		$input = [
			'path' => 'SomeCommand',
			'--properties' => 'first, last',
			'base' => 'foo'
		];

		$stub = 'class SomeCommandHandler {}';

		$file->get(Argument::any())->willReturn('template');
		$parser->parse($input['path'], $input['--properties'])->willReturn([]);
		$mustache->render('template', [])->shouldBeCalled()->willReturn($stub);

		$file->put('foo/'.$input['path'].'Handler.php', $stub)->shouldBeCalled();

		$this->generateHandlerClass($input['path'], $input['--properties'], $input['base']);
	}

}
