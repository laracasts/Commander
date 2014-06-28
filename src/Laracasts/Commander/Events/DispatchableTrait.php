<?php  namespace Laracasts\Commander\Events;

 use App;

 trait DispatchableTrait {

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
      * Get the event dispatcher.
      *
      * @return \Laracasts\Commander\Events\EventDispatcher
      */
     public function getDispatcher()
     {
         return App::make('Laracasts\Commander\Events\EventDispatcher');
     }
 }