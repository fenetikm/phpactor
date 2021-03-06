<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Response\Input\Input;
use Phpactor\Rpc\Response\InputCallbackResponse;
use Phpactor\Rpc\Request;

abstract class AbstractHandler implements Handler
{
    private $requiredArguments = [];

    protected function requireInput(Input $input)
    {
        $this->requiredArguments[$input->name()] = $input;
    }

    protected function hasMissingArguments(array $arguments)
    {
        if ($this->missingArguments($arguments)) {
            return true;
        }

        return false;
    }

    protected function createInputCallback(array $arguments)
    {
        return InputCallbackResponse::fromCallbackAndInputs(
            Request::fromNameAndParameters(
                $this->name(),
                $arguments
            ),
            $this->inputsFromMissingArguments($arguments)
        );
    }

    private function missingArguments(array $arguments): array
    {
        return array_keys(array_filter($arguments, function ($argument, $key) {
            if (false === isset($this->requiredArguments[$key])) {
                return false;
            }

            return empty($argument);
        }, ARRAY_FILTER_USE_BOTH));
    }

    private function inputsFromMissingArguments(array $arguments)
    {
        $inputs = [];
        foreach ($this->missingArguments($arguments) as $argumentName) {
            if (false === isset($this->requiredArguments[$argumentName])) {
                throw new \InvalidArgumentException(sprintf(
                    'Parameter "%s" is not set and no interactive input was made available for it',
                    $argumentName
                ));
            }

            $inputs[] = $this->requiredArguments[$argumentName];
        }

        return array_reverse($inputs);
    }
}
