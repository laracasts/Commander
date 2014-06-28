<?php namespace Laracasts\Commander;

interface CommandBus {

    public function execute($command);

}