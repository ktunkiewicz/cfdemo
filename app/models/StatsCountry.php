<?php

/**
 * Class for holding temporary statistic data
 */
class StatsCountry {
    
    private $data = [];
    private $min = 999;
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
     * Populate model with data
     * 
     * @param array $data
     */
    public function feed($data) {
        $this->count++;
        $this->tempRateSum += $data['rate'];
        $this->totalSell += $data['amountSell'];
        if ($data['rate']<$this->min && $data['rate']!== 0) { $this->min = $data['rate']; }
        if ($data['rate']>$this->max ) { $this->max = $data['rate']; }
    }
    
    /**
     * Compute container and return calculation
     */
    public function compute() {
        
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
            'tempRateSum' => $this->tempRateSum,
        ];
    }
}
