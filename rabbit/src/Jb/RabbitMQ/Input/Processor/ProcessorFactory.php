<?php

namespace Jb\RabbitMQ\Input\Processor;

/**
 * Description of ProcessorFactory
 *
 * @author jobou
 */
class ProcessorFactory
{
    /**
     * Instantiate the right processor for an argument or option
     *
     * @param string $name hte option or argument name
     *
     * @return \Jb\RabbitMQ\Input\Processor\ProcessorInterface|false
     */
    public static function getProcessor($name)
    {
        $processorClass = __NAMESPACE__."\\".ucfirst($name)."Processor";
        if (class_exists($processorClass)) {
            return new $processorClass();
        }

        return false;
    }
}
