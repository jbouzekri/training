<?php

namespace Jb\RabbitMQ\Message;

/**
 *
 * @author jobou
 */
interface MessageFactoryInterface
{
    /**
     * Build the AMQPMessage
     *
     * @param int $number
     *
     * @return \PhpAmqpLib\Message\AMQPMessage
     */
    public function buildAMQPMessage($number);
}
