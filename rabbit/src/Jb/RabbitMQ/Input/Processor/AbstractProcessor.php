<?php

namespace Jb\RabbitMQ\Input\Processor;


/**
 * Process inputs options or arguments
 *
 * @author jobou
 */
abstract class AbstractProcessor
{
    /**
     * Validate an entry
     *
     * @throws \RuntimeException
     */
    public abstract function validate($value);

    /**
     * fill with default value
     *
     * @param string $value
     *
     * @return string
     */
    public abstract function setDefault($value);

    /**
     * Explode and trim
     *
     * @param string $value
     *
     * @return array
     */
    public function cleanExplode($value)
    {
        return array_map('trim', explode(',', $value));
    }
}
