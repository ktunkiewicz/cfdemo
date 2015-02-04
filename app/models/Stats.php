<?php

/**
 * Class for holding temporary statistic data
 */
class Stats {
    
    private $timespans = [];

    private $count = 0;
    
    /**
     * Creates data container
     * 
     * @param array $timespans
     * @param array $rateTypes
     * @param array $countries
     */
    public function __construct($timespans) {
        foreach ($timespans as $timespan) {
            $this->timespans[$timespan] = new StatsTime();
        }
    }
    
    /**
     * Populate models with data
     * 
     * @param array $data
     */
    public function feed($data,$timespan) {
        $this->timespans[$timespan]->feed($data);
    }
    
    /**
     * Compute data from child models
     * 
     * @return type
     */
    public function compute() {
        
        $results = [];
        foreach($this->timespans as $type=>$timespan) {
            $result = $timespan->compute();
            $results[$type] = $result;
            $this->count += $result->count;
        }
        
        return (object)[
            'count' => $this->count,
            'timespans' => $results,
        ];
        
    }
    
    public function __get($key) {
        return isset($this->timespans[$key]) ? $this->timespans[$key] : null;
    }
    
}
