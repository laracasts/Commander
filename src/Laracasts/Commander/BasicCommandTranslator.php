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
        $handler = $this->replace('CommandHandler', $command);

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
        return $this->replace('Validator', $command);
    }
    
    /**
     * String replacer
     *
     * @param $with, $command
     * @return string
     */
    protected function replace($with, $command)
	{
		$commandClass = get_class($command);
		return substr_replace($commandClass, $with, strrpos($commandClass, 'Command'));
	}

} 
