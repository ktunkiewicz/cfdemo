<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Ratchet\Server\IoServer;
use \Broadcaster;

class BroadcasterCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'broadcaster:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts socket server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
            parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Starting socket server');
	$broadcaster = new Broadcaster;
	
	$loop   = React\EventLoop\Factory::create();	
	$context = new React\ZMQ\Context($loop);
	
	// Internal sockets
	$pull = $context->getSocket(ZMQ::SOCKET_PULL);
	$pull->bind('tcp://127.0.0.1:5555');
	$pull->on('message', array($broadcaster, 'onNewEntry'));
	
	// External sockets
	$webSock = new React\Socket\Server($loop);
	$webSock->listen(8081, '0.0.0.0');

	$webServer = new Ratchet\Server\IoServer(
            new Ratchet\Http\HttpServer(
                new Ratchet\WebSocket\WsServer(
                    new Ratchet\Wamp\WampServer(
                        $broadcaster
                    )
                )
            ),
            $webSock
        );

        $loop->run();
	
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
            return array(
            );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
            return array(
                    array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
            );
    }

}
