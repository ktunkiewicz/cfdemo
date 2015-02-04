<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Jenssegers\Mongodb\Model as Eloquent;

class Messages extends Eloquent {

    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'messages';
    public $timestamps = false;
        
}
