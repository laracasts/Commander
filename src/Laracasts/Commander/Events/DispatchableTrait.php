<?php  namespace Laracasts\Commander\Events;

use Laracasts\Commander\Events\Contracts\Dispatcher;
use App;

 trait DispatchableTrait {

     /**
      * The Dispatcher instance.
      *
      * @var Dispatcher
      */
     protected $dispatcher;

     /**
      * Dispatch all events for an entity.
      *
      * @param object $entity
      */
     public function dispatchEventsFor($entity)
     {
         return $this->getDispatcher()->dispatch($entity->releaseEvents());
     }

     /**
      * Set the dispatcher instance.
      *
      * @param mixed $dispatcher
      */
     public function setDispatcher(Dispatcher $dispatcher)
     {
         $this->dispatcher = $dispatcher;
     }

     /**
      * Get the event dispatcher.
      *
      * @return Dispatcher
      */
     public function getDispatcher()
     {
         return $this->dispatcher ?: App::make('Laracasts\Commander\Events\EventDispatcher');
     }

 }