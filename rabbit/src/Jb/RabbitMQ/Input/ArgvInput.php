<?php

namespace Jb\RabbitMQ\Input;

use Symfony\Component\Console\Input\ArgvInput as BaseArgvInput;

/**
 * Description of ArgvInput
 *
 * @author jobou
 */
class ArgvInput extends BaseArgvInput
{
    /**
     * Validates the input.
     *
     * @throws \RuntimeException When not enough arguments are given
     */
    public function validate()
    {
        parent::validate();

        $this->processOptions();
    }

    /**
     * Validate and set default for options
     */
    private function processOptions()
    {
        foreach ($this->getOptions() as $name => $value) {
            if (!$this->hasOption($name)) {
                continue;
            }

            $processor = Processor\ProcessorFactory::getProcessor($name);
            if ($processor instanceof Processor\AbstractProcessor) {
                $processor->validate($value);

                $this->setOption($name, $processor->setDefault($value));
            }
        }
    }
}
