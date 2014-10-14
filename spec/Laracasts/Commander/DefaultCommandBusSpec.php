<?php

namespace spec\Laracasts\Commander;

use Laracasts\Commander\BasicCommandTranslator;
use Laracasts\Commander\CommandBus;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Illuminate\Foundation\Application;
use Laracasts\Commander\CommandTranslator;

class DefaultCommandBusSpec extends ObjectBehavior {

    function let(Application $app, CommandTranslator $translator)
    {
        $this->beConstructedWith($app, $translator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Laracasts\Commander\DefaultCommandBus');
    }

    function it_handles_a_command(Application $app, CommandStub $command, CommandTranslator $translator, CommandHandlerStub $handler)
    {
        $translator->toCommandHandler($command)->willReturn('CommandHandler');
        $app->make('CommandHandler')->willReturn($handler);
        $handler->handle($command)->shouldBeCalled();

        $this->execute($command);
    }

    function it_can_trigger_decorators_before_calling_the_handler(Application $app, CommandStub $command, CommandTranslator $translator, CommandHandlerStub $handler)
    {
        $translator->toCommandHandler($command)->willReturn('CommandHandler');
        $app->make('CommandHandler')->willReturn($handler);
        $handler->handle($command)->shouldBeCalled();

        // If we specify a decorator before calling execute(), that decorator should be
        // resolved out of the container and executed first.
        $decorator = 'spec\Laracasts\Commander\CommandBusDecoratorStub';
        $app->make($decorator)->shouldBeCalled()->willReturn(new CommandBusDecoratorStub);

        $this->decorate($decorator)->execute($command);
    }



}

// Stub Stuff
class CommandStub {}
class CommandHandlerStub { public function handle($command) {} }
class CommandBusDecoratorStub implements \Laracasts\Commander\CommandBus { public function execute($command) {} }

namespace Illuminate\Foundation;
class Application { function make() {} }
