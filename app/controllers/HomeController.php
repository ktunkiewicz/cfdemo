<?php

class HomeController extends Controller {

	public function index()
	{
	    $cur_from = Messages::distinct('currencyFrom')->get()->toArray();
	    $cur_to = Messages::distinct('currencyTo')->get()->toArray();
	    $variants = [];
	    foreach($cur_from as $from) {
		foreach($cur_to as $to) {
		    $variants[] = $from[0] . '_' . $to[0];
		}
	    }
	    return View::make('frontpage.index', compact('variants'));
	}

}
