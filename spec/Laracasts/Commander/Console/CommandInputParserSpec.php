<?php namespace spec\Laracasts\Commander\Console;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommandInputParserSpec extends ObjectBehavior {

    function it_is_initializable()
    {
        $this->shouldHaveType('Laracasts\Commander\Console\CommandInputParser');
    }

    function it_returns_an_instance_of_command_input()
    {
        $this->parse('Foo/Bar/MyCommand', 'username, email')
            ->shouldBeAnInstanceOf('Laracasts\Commander\Console\CommandInput');
    }

    function it_parses_the_name_of_the_class()
    {
        $input = $this->parse('Foo/Bar/MyCommand', 'username, email');

        $input->name->shouldBe('MyCommand');
    }

    function it_parses_the_namespace_of_the_class()
    {
        $input = $this->parse('Foo/Bar/MyCommand', 'username, email');

        $input->namespace->shouldBe('Foo\Bar');
    }

    function it_parses_the_properties_for_the_class()
    {
        $input = $this->parse('Foo/Bar/MyCommand', 'username, email');

        $input->properties->shouldBe(['username', 'email']);
    }


}
