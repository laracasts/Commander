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
        $commandClass = get_class($command);
        $handler = substr_replace($commandClass, 'CommandHandler', strrpos($commandClass, 'Command'));

        if ( ! class_exists($handler))
        {
            $message = "Command handler [$handler] does not exist.";

            throw new HandlerNotRegisteredException($message);
        }

        return $handler;
    }

} 