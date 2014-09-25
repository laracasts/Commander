<?php namespace spec\Laracasts\Commander\Events;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Laracasts\Commander\Events\Contracts\Dispatcher;

class DispatchableSpec extends ObjectBehavior {

    function let()
    {
        $this->beAnInstanceOf(HandlerStub::class);
    }

    function it_dispatches_stuff(Dispatcher $dispatcher)
    {
        $this->setDispatcher($dispatcher);

        $this->dispatchEventsFor(new EntityStub);

        $dispatcher->dispatch([])->shouldBeCalled();
    }

}


class HandlerStub {
    use \Laracasts\Commander\Events\DispatchableTrait;
}

class EntityStub {

    public function releaseEvents()
    {
        return [];
    }

}