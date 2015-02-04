<?php

/**
 * Class for holding temporary statistic data
 */
class StatsRate {
    
    private $countries = [];
    
    private $min = 0;
    private $max = 0;
    private $avg = 0;
    private $totalSell = 0;
    private $avgSell = 0;
    private $count = 0;
    
    private $tempRateSum = 0;
        
    public function __get($key) {
        return isset($this->countries[$key]) ? $this->countries[$key] : null;
    }

    /**
     * Populate child models with data
     * 
     * @param type $data
     */
    public function feed($data) {
        
        // Create child if it doesn't exists        
        if (!isset($this->countries[$data['originatingCountry']])) {
            $this->countries[$data['originatingCountry']] = new StatsCountry();
        }
        // Populate with data
        $this->countries[$data['originatingCountry']]->feed($data);
    }
    
    /**
     * Compute data from child models
     * 
     * @return type
     */
    public function compute() {
        
        $results = [];
        foreach($this->countries as $code=>$country) {
            $result = $country->compute();
            $results[$code] = $result;
            $this->tempRateSum += $result->tempRateSum;
            $this->totalSell += $result->totalSell;
            if ($result->min < $this->min || $this->count == 0) { $this->min = $result->min; }
            if ($result->max > $this->max || $this->count == 0) { $this->max = $result->max; }
            $this->count += $result->count;
        }
        if($this->count) {
            $this->avg = $this->tempRateSum / $this->count;
            $this->avgSell = $this->totalSell / $this->count;        
        } else {
            $this->avg = 0;
            $this->avgSell = 0;
        }
                
        return (object)[
            'min' => $this->min,
            'max' => $this->max,
            'avg' => $this->avg,
            'totalSell' => $this->totalSell,
            'avgSell' => $this->avgSell,
            'count' => $this->count,
            'countries' => $results,
            'tempRateSum' => $this->tempRateSum,            
        ];
        
    }
    
    
}
