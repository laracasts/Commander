<?php namespace Laracasts\Commander;

interface CommandBus {

    /**
     * Execute a command
     *
     * @param $command
     * @return mixed
     */
    public function execute($command);

}