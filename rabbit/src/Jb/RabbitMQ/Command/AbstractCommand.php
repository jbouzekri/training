<?php

namespace Jb\RabbitMQ\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of Command
 *
 * @author jobou
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var \PhpAmqpLib\Connection\AMQPConnection
     */
    protected $connection;

    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected $channel;

    /**
     * Add the command name and description
     */
    protected abstract function appendConfiguration();

    /**
     * Configure common parameters for all rabbit mq command
     */
    protected final function configure()
    {
        $options = array(
            array('host', null, InputOption::VALUE_OPTIONAL, 'Hostname of the rabbit server', RABBIT_HOST),
            array('port', null, InputOption::VALUE_OPTIONAL, 'Port of the rabbit server', RABBIT_PORT),
            array('user', null, InputOption::VALUE_OPTIONAL, 'User to connect to the rabbit server', RABBIT_USER),
            array('pass', null, InputOption::VALUE_OPTIONAL, 'Password to connect to the rabbit server', RABBIT_PASS),
            array('vhost', null, InputOption::VALUE_OPTIONAL, 'vhost of the rabbit server', RABBIT_VHOST),
            array(
                'queue',
                null,
                InputOption::VALUE_REQUIRED,
                "create a new or use an existing queue when connecting\n"
                . "\n"
                . "Format : <name>(string),<passive>(true|false),<durable>(true|false),"
                . "<exclusive>(true|false),<auto_delete>(true|false)\n"
                . "\n"
                . "durable: true -> the queue will survive server restarts\n"
                . "exclusive: false -> the queue can be accessed in other channels\n"
                . "auto_delete: false -> the queue won't be deleted once the channel is closed\n"
                . "\n"
                . "<name>,<fg=yellow>false,true,false,false</fg=yellow>"
                . "\n",
                null
            ),
            array(
                'exchange',
                null,
                InputOption::VALUE_REQUIRED,
                "create a new or use an existing exchange when connecting\n"
                . "\n"
                . "Format : <name>(string),<type>(string),<passive>(true|false),<durable>(true|false),"
                . "<auto_delete>(true|false)\n"
                . "\n"
                . "durable: true -> the exchange will survive server restarts\n"
                . "auto_delete: false -> the exchange won't be deleted once the channel is closed\n"
                . "\n"
                . "<name>,<fg=yellow>'direct',false,true,false</fg=yellow>"
                . "\n",
                null
            ),
            array(
                'bind',
                null,
                InputOption::VALUE_REQUIRED,
                "Bind information between exchange and queue\n"
                . "\n"
                . "Format : <exchange name>(string),<queue name>(string),<routing key>(string)\n"
                . "\n"
                . "<exchange name>,<queue name>,<fg=yellow>''</fg=yellow>"
                . "\n",
                null
            ),
            array(
                'only-config',
                null,
                InputOption::VALUE_NONE,
                'Only execute rabbit configuration. Do not publish or consume.',
                null
            ),
            array('debug', null, InputOption::VALUE_NONE, 'Activate rabbitmq library debug', null),
        );

        foreach ($options as $value) {
            $this->addOption($value[0], $value[1], $value[2], $value[3], $value[4]);
        }

        $this->appendConfiguration();
    }

    /**
     * Execute common logic for all rabbitmq command
     * The command actions are in the process method.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public final function execute(InputInterface $input, OutputInterface $output)
    {
        // Open connection to RabbitMQ
        $this->connection = new \PhpAmqpLib\Connection\AMQPConnection(
            $input->getOption('host'),
            $input->getOption('port'),
            $input->getOption('user'),
            $input->getOption('pass'),
            $input->getOption('vhost')
        );

        // Create a channel for transport (same as a subsocket in the socket)
        $this->channel = $this->connection->channel();

        // If queue option, declare the queue
        if (!is_null($input->getOption('queue'))) {
            $queue = array_map('trim', explode(',', $input->getOption('queue')));

            $queueName = $queue[0];
            $this->channel->queue_declare(
                $queueName,
                filter_var($queue[1], FILTER_VALIDATE_BOOLEAN),
                filter_var($queue[2], FILTER_VALIDATE_BOOLEAN),
                filter_var($queue[3], FILTER_VALIDATE_BOOLEAN),
                filter_var($queue[4], FILTER_VALIDATE_BOOLEAN)
            );
        }

        // If exchange option, declare the exchange
        if (!is_null($input->getOption('exchange'))) {
            $exchange = array_map('trim', explode(',', $input->getOption('exchange')));

            $exchangeName = $exchange[0];
            $this->channel->exchange_declare(
                $exchangeName,
                $exchange[1],
                filter_var($exchange[2], FILTER_VALIDATE_BOOLEAN),
                filter_var($exchange[3], FILTER_VALIDATE_BOOLEAN),
                filter_var($exchange[4], FILTER_VALIDATE_BOOLEAN)
            );
        }

        // If bind option, bind an exchange to a queue
        if (!is_null($input->getOption('bind'))) {
            $bind = array_map('trim', explode(',', $input->getOption('bind')));

            $this->channel->queue_bind($bind[1], $bind[0], $bind[2]);
        }
        // Else if both queue and exchange option, bind each other
        elseif (isset($queueName) && isset($exchangeName)) {
            $this->channel->queue_bind($queue, $exchange);
        }

        // Always close connection and channel on exit
        register_shutdown_function(array($this, 'shutdown'), $this->channel, $this->connection);

        // If not only config execute publish/consume process
        if (!$input->getOption("only-config")) {
            $this->process($input, $output);
        }
    }

    /**
     * Shutdown function to cleanly close connection and channel
     *
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param \PhpAmqpLib\Connection\AMQPConnection $connection
     */
    public function shutdown(\PhpAmqpLib\Channel\AMQPChannel $channel, \PhpAmqpLib\Connection\AMQPConnection $connection)
    {
        $channel->close();
        $connection->close();
    }

    /**
     * Execute command custom actions
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected abstract function process(InputInterface $input, OutputInterface $output);
}
