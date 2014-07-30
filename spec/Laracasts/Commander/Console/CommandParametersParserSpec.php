<?php

namespace spec\Laracasts\Commander\Console;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommandParametersParserSpec extends ObjectBehavior
{
	function it_parses_the_command_input()
	{
		$path = 'Foo/Bar/Baz';
		$properties = 'first, middle, last';

		$this->parse($path, $properties)->shouldReturn([
			'name' => 'Baz',
			'namespace' => 'Foo\Bar',
			'arguments' => '$first, $middle, $last',
			'properties' => ['first', 'middle', 'last']
		]);
	}

    function it_sets_properties_and_arguments_to_null_if_none_are_passed()
    {
        $path = 'Foo/Bar/Baz';

        $this->parse($path, null)->shouldReturn([
            'name' => 'Baz',
            'namespace' => 'Foo\Bar',
            'arguments' => null,
            'properties' => null
        ]);
    }
}
