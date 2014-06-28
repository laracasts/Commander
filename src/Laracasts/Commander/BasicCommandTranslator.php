<?php namespace Laracasts\Commander;

class BasicCommandTranslator implements CommandTranslator {

    /**
     * Translate a command to its handler counterpart
     *
     * @param $command
     * @return mixed
     * @throws HandlerNotRegisteredException
     */
    public function toCommandHandler($command)
    {
        $handler = str_replace('Command', 'CommandHandler', get_class($command));

        if ( ! class_exists($handler))
        {
            $message = "Command handler [$handler] does not exist.";

            throw new HandlerNotRegisteredException($message);
        }

        return $handler;
    }

    /**
     * Translate a command to its validator counterpart
     *
     * @param $command
     * @return mixed
     */
    public function toValidator($command)
    {
        return str_replace('Command', 'Validator', get_class($command));
    }

} 