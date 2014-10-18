<?php

namespace Jb\RabbitMQ\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Description of PublisherCommand
 *
 * @author jobou
 */
class PublisherCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function appendConfiguration()
    {
        $this
            ->setName('rabbitmq:publisher')
            ->setDescription('Create and start a publisher')
            ->addArgument('exchange', InputArgument::OPTIONAL, 'The name of the exchange', 'router')
            ->addOption(
                'iteration',
                null,
                InputOption::VALUE_OPTIONAL,
                'The number of messages send',
                10
            )
            ->addOption(
                'waiting_time',
                null,
                InputOption::VALUE_OPTIONAL,
                'The sleeping time between each message (0 : no time)',
                1
            )
            ->addOption(
                'message_type',
                null,
                InputOption::VALUE_OPTIONAL,
                'The type of message send',
                'plain'
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function process(InputInterface $input, OutputInterface $output)
    {
        $factory = \Jb\RabbitMQ\Message\MessageBuilder::create($input->getOption('message_type'));

        $waitingTime = (int) $input->getOption('waiting_time');

        // Loop to publish message
        $number = 1;
        while ($number < (int) $input->getOption('iteration')) {
            $this->channel->basic_publish($factory->buildAMQPMessage($number), $input->getArgument('exchange'));
            if ($waitingTime !== 0) {
                sleep($waitingTime);
            }
            $number++;
        }

        // Always send quit message at the end
        $quitMessage = new \PhpAmqpLib\Message\AMQPMessage('quit');
        $this->channel->basic_publish($quitMessage, $input->getArgument('exchange'));
    }
}