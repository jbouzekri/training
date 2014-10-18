<?php

namespace Jb\RabbitMQ\Input\Processor;

/**
 * Description of QueueProcessor
 *
 * @author jobou
 */
class QueueProcessor extends AbstractProcessor
{
    /**
     * {@inheritDoc}
     */
    public function validate($value)
    {
        if (is_null($value)) {
            return true;
        }

        $configuration = explode(',', $value);
        if (count($configuration) == 0 || count($configuration) > 5) {
            throw new \RuntimeException('Queue error : number');
        }

        foreach ($configuration as $position => $value) {
            if (!is_string($value)) {
                throw new \RuntimeException('Queue data must be string');
            }

            if ($position > 0 && !in_array($value, array('true', 'false'))) {
                throw new \RuntimeException('Queue : boolean wrong format : use true or false string');
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

        if (!isset($configuration[1])) {
            $configuration[1] = 'false';
        }
        if (!isset($configuration[2])) {
            $configuration[2] = 'true';
        }
        if (!isset($configuration[3])) {
            $configuration[3] = 'false';
        }
        if (!isset($configuration[4])) {
            $configuration[4] = 'false';
        }

        return implode(',', $configuration);
    }
}
