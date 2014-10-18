<?php

namespace Jb\RabbitMQ\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Description of ConsumerCommand
 *
 * @author jobou
 */
class ConsumerCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function appendConfiguration()
    {
        $this
            ->setName('rabbitmq:consumer')
            ->setDescription('Create and start a consumer')
            ->addArgument('consumer_tag', InputArgument::OPTIONAL, 'The identifier of the consumer', 'consumer')
            ->addArgument('queue_name', InputArgument::OPTIONAL, 'The name of the queue to consume from', 'messages')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function process(InputInterface $input, OutputInterface $output)
    {
        $this->channel->basic_consume(
            $input->getArgument('queue_name'),
            $input->getArgument('consumer_tag'),
            false,
            false,
            false,
            false,
            function ($msg) use ($output) {
                $output->writeln($msg->body);

                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

                // Send a message with the string "quit" to cancel the consumer.
                if ($msg->body === 'quit') {
                    $msg->delivery_info['channel']->basic_cancel($msg->delivery_info['consumer_tag']);
                }
            }
        );

        // Loop as long as the channel has callbacks registered
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }
}
