<?php namespace Laracasts\Commander;

interface CommandHandler {

    /**
     * Handle the command.
     *
     * @param Command $command
     * @return mixed
     */
    public function handle(Command $command);

} 
