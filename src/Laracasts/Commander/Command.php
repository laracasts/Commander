<?php namespace Laracasts\Commander;

interface Command {

    /**
     * Return an Array of self
     * @return array
     */
    public function toArray();
}
