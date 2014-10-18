<?php

namespace Jb\RabbitMQ\Message;

/**
 * Description of MessageBuilder
 *
 * @author jobou
 */
class MessageBuilder
{
    /**
     * An array of available message type factory
     *
     * @var array
     */
   public static $messageFactory = array(
       "plain" => "Jb\\RabbitMQ\\Message\\PlainMessageFactory"
   );

    /**
     * Build a message
     *
     * @param string $type the type of the message
     * @param int $number the number of the message
     *
     * @return \Jb\RabbitMQ\Message\MessageFactoryInterface
     */
    public static function create($type)
    {
        return new self::$messageFactory[$type];
    }
}
