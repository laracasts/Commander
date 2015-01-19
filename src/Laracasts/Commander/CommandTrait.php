<?php namespace Laracasts\Commander;

trait CommandTrait {

    /**
     * Return an Array of self
     * @return array
     */
    public function toArray()
    {
        return (array)$this;
    }
}
