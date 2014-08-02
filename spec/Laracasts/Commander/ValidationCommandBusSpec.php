<?php

namespace spec\Laracasts\Commander;

use Illuminate\Foundation\Application;
use Laracasts\Commander\CommandBus;
use Laracasts\Commander\CommandTranslator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ValidationCommandBusSpec extends ObjectBehavior
{
    function let(CommandBus $bus, Application $application, CommandTranslator $translator)
    {
        $this->beConstructedWith($bus, $application, $translator);
    }

    function it_does_not_handle_command_if_validation_fails(
        Application $application,
        CommandTranslator $translator,
        CommandBus $bus,
        ExampleCommand $command,
        ExampleValidator $validator
    ) {
        // Own responsibility
        $translator->toValidator($command)->willReturn(ExampleValidator::class);
        $application->make(ExampleValidator::class)->willReturn($validator);
        $validator->validate($command)->willThrow('RuntimeException');

        // Delegated responsibility
        $bus->execute($command)->shouldNotBeCalled();

        $this->shouldThrow('RuntimeException')->duringExecute($command);
    }

    function it_handles_command_if_validation_succeeds(
        Application $application,
        CommandTranslator $translator,
        CommandBus $bus,
        ExampleCommand $command,
        ExampleValidator $validator
    ) {
        // Own responsibility
        $translator->toValidator($command)->willReturn(ExampleValidator::class);
        $application->make(ExampleValidator::class)->willReturn($validator);

        // Delegated responsibility
        $bus->execute($command)->shouldBeCalled();

        $this->execute($command);
    }
}

// Stub Stuff
class ExampleCommand {}
class ExampleValidator { public function validate($command) {} }

namespace Illuminate\Foundation;
class Application { function make() {} }