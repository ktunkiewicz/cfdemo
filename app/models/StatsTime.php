<?php

/**
 * Class for holding temporary statistic data
 */
class StatsTime {
    
    private $rateTypes = [];
    
    private $min = 0;
    private $max = 0;
    private $avg = 0;
    private $totalSell = 0;
    private $avgSell = 0;
    private $count = 0;
    
    private $tempRateSum = 0;

    public function __get($key) {
        return isset($this->rateTypes[$key]) ? $this->rateTypes[$key] : null;
    }

    /**
     * Populate child models with data
     * 
     * @param type $data
     */
    public function feed($data) {
        
        // Create child if it doesn't exists
        if (!isset($this->rateTypes[$data['currencyFrom'].'_'.$data['currencyTo']])) {
            $this->rateTypes[$data['currencyFrom'].'_'.$data['currencyTo']] = new StatsRate();
        }
        
        // Populate with data
        $this->rateTypes[$data['currencyFrom'].'_'.$data['currencyTo']]->feed($data);
    }
    
    /**
     * Compute data from child models
     * 
     * @return type
     */
    public function compute() {
        
        $results = [];
        foreach($this->rateTypes as $code=>$rate) {
            $result = $rate->compute();
            $results[$code] = $result;
            $this->count += $result->count;
        }
        
        return (object)[
            'count' => $this->count,
            'rateTypes' => $results,
        ];
        
    }
    
    
}
