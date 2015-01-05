<?php namespace Laracasts\Commander;

interface CommandValidator {

    /**
     * Validate the command
     *
     * @param $command
     * @return mixed
     */
    public function validate($command);

} 
