<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Jenssegers\Mongodb\Model as Eloquent;

class ProcessorCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'processor:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts data processor';

    /**
     * Time spans for statistics
     * 
     */
    //protected $timespans = ['minute','hour','day','month'];
    protected $timespans = ['minute'];
    
    
    /**
     * Data model
     * @var \Stats 
     */
    protected $stats;


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
        $this->info('Starting data processor server');

        /*
         * Main loop starts here
         * it will be triggered each 500ms
         */
        
        $this->stats = new Stats($this->timespans);
        
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'cfdemo_pusher');
        $socket->connect("tcp://localhost:5555");
        
        while(true) {
            foreach($this->timespans as $timespan) {
                $query = Messages::where('timePlaced', '>', new DateTime('-1 '.$timespan))->get();
                if ($query->count()) {
                    foreach($query as $row) {
                        $this->stats->feed($row, $timespan);
                    }
                }
            }
            
            $results = $this->stats->compute();
            $socket->send(json_encode($results));

            usleep(500);
        }
        
        
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
