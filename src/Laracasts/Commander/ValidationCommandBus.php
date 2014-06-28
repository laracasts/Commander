<?php namespace Laracasts\Commander;

class ValidationCommandBus extends DefaultCommandBus {

    /**
     * Execute a command with validation.
     *
     * @param $command
     * @return mixed
     */
    public function execute($command)
    {
        // If a validator is "registered," we will
        // first trigger it, before moving forward.
        $this->validateCommand($command);

        // When we're done, we'll move up the stack
        // and handle the rest.
        return parent::execute($command);
    }

    /**
     * If appropriate, validate command data.
     *
     * @param $command
     */
    protected function validateCommand($command)
    {
        $validator = $this->commandTranslator->toValidator($command);

        if (class_exists($validator))
        {
            $this->app->make($validator)->validate($command);
        }
    }

}