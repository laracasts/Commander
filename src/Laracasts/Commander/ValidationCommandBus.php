<?php namespace Laracasts\Commander;

class ValidationCommandBus extends DefaultCommandBus {

    public function execute($command)
    {
        $validator = $this->commandTranslator->toValidator($command);

        if (class_exists($validator))
        {
            $this->app->make($validator)->validate($command);
        }

        return parent::execute($command);
    }

}