<?php

namespace Jb\RabbitMQ\Message;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Description of PlainMessage
 *
 * @author jobou
 */
class PlainMessageFactory implements MessageFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function buildAMQPMessage($number)
    {
        $msg_body = 'message '.$number;
        return new AMQPMessage($msg_body, array('content_type' => 'text/plain', 'delivery_mode' => 2));
    }
}
