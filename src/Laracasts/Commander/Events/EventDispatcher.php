<?php namespace Laracasts\Commander\Events;

use Illuminate\Events\Dispatcher;
use Illuminate\Log\Writer;

class EventDispatcher {

    /**
     * @var Dispatcher
     */
    protected $event;

    /**
     * @var Writer
     */
    protected $log;

    /**
     * @param Dispatcher $event
     * @param Writer $log
     */
    function __construct(Dispatcher $event, Writer $log)
    {
        $this->event = $event;
        $this->log = $log;
    }

    /**
     * Dispatch all events
     *
     * @param array $events
     */
    public function dispatch(array $events)
    {
        foreach($events as $event)
        {
            $eventName = $this->getEventName($event);

            $this->event->fire($eventName, $event);

            $this->log->info("{$eventName} was fired.");
        }
    }

    /**
     * We'll make the fired event name look
     * just a bit more object-oriented.
     *
     * @param $event
     * @return mixed
     */
    protected function getEventName($event)
    {
        return str_replace('\\', '.', get_class($event));
    }

} 