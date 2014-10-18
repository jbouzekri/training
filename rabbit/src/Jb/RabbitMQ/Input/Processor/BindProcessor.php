<?php

namespace Jb\RabbitMQ\Input\Processor;

/**
 * Description of QueueProcessor
 *
 * @author jobou
 */
class BindProcessor extends AbstractProcessor
{
    /**
     * {@inheritDoc}
     */
    public function validate($value)
    {
        if (is_null($value)) {
            return true;
        }

        $configuration = $this->cleanExplode($value);
        if (count($configuration) < 2 || count($configuration) > 3) {
            throw new \RuntimeException('Bind error : number');
        }

        foreach ($configuration as $position => $value) {
            if (!is_string($value) || empty($value)) {
                throw new \RuntimeException('Bind data must be string and not empty');
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setDefault($value)
    {
        if (is_null($value)) {
            return null;
        }

        $configuration = $this->cleanExplode($value);

        if (!isset($configuration[2])) {
            $configuration[2] = '';
        }

        return implode(',', $configuration);
    }
}
