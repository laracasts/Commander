<?php

namespace spec\Laracasts\Commander\Console;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Illuminate\Filesystem\Filesystem;
use Mustache_Engine;
use Laracasts\Commander\Console\CommandInputParser;
use Laracasts\Commander\Console\CommandInput;

class CommandGeneratorSpec extends ObjectBehavior {

    function let(Filesystem $file, Mustache_Engine $mustache)
    {
        $this->beConstructedWith($file, $mustache);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Laracasts\Commander\Console\CommandGenerator');
    }

    function it_generates_a_command_class(Filesystem $file, Mustache_Engine $mustache)
    {
        $input = new CommandInput('SomeCommand', 'Acme\Bar', ['name', 'email'], '$name, $email');
        $template = 'foo.stub';
        $destination = 'app/Acme/Bar/SomeCommand.php';

        $file->get($template)->shouldBeCalled()->willReturn('template');
        $mustache->render('template', $input)->shouldBeCalled()->willReturn('stub');
        $file->put($destination, 'stub')->shouldBeCalled();

        $this->make($input, $template, $destination);
    }

}
