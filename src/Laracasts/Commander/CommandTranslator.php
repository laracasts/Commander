<?php namespace Laracasts\Commander;

interface CommandTranslator {

    /**
     * Translate a command to its handler counterpart.
     *
     * @param $command
     * @return mixed
     * @throws Exception
     */
    public function toCommandHandler($command);

}