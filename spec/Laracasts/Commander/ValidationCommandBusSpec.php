<?php

namespace spec\Laracasts\Commander;

use Illuminate\Foundation\Application;
use Laracasts\Commander\CommandHandler;
use Laracasts\Commander\CommandTranslator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ValidationCommandBusSpec extends ObjectBehavior
{
    function let(Application $application, CommandTranslator $translator)
    {
        $this->beConstructedWith($application, $translator);
    }

    function it_does_not_handle_command_if_validation_fails(
        Application $application,
        CommandTranslator $translator,
        CommandHandler $handler,
        ExampleCommand $command,
        ExampleValidator $validator
    ) {
        // Responsibility group #1
        $translator->toValidator($command)->willReturn(ExampleValidator::class);
        $application->make(ExampleValidator::class)->willReturn($validator);
        $validator->validate($command)->willThrow('RuntimeException');

        // Responsibility group #2
        $translator->toCommandHandler($command)->willReturn(ExampleCommand::class);
        $application->make(ExampleCommand::class)->willReturn($handler);
        $handler->handle($command)->shouldNotBeCalled();

        $this->shouldThrow('RuntimeException')->duringExecute($command);
    }

    function it_handles_command_if_validation_succeeds(
        Application $application,
        CommandTranslator $translator,
        CommandHandler $handler,
        ExampleCommand $command,
        ExampleValidator $validator
    ) {
        // Responsibility group #1
        $translator->toValidator($command)->willReturn(ExampleValidator::class);
        $application->make(ExampleValidator::class)->willReturn($validator);

        // Responsibility group #2
        $translator->toCommandHandler($command)->willReturn(ExampleCommand::class);
        $application->make(ExampleCommand::class)->willReturn($handler);
        $handler->handle($command)->shouldBeCalled();

        $this->execute($command);
    }
}

class ExampleCommand {}
class ExampleValidator { public function validate($command) {} }
