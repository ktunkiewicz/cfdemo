/**
 * @file This is the REST endpoint feeder script to populate the database with data
 * @author Kamil Tunkiewicz <kamil.tunkiewicz@gmail.com>
 * @license MIT
 */

var rest = require('restler');
var config = require('./config');

function randomUser() {
    return Math.round(99998 * Math.random()) + 1;
}

function randomSell() {
    return Math.round(5000 * Math.random()) + 1;
}

function randomRate() {
    return parseFloat(0.7471 + (-0.5 + Math.random()) / 10).toFixed(4);
}

function randomCountry() {
    var i = parseInt(10 * Math.random());
    var countries = ['IE','GB','FR','DE','BE','ES','IT','PL','NL','DK'];
    return countries[i];
}

/*
 * This goes round and round doing the same, stop it with CTRL+C
 */

setInterval(function(){
    var rate = randomRate();
    var sell = randomSell();
    var d = new Date();
    rest.postJson('http://' + config.host + ':' + config.port, {
        "userId": randomUser(),
        "currencyFrom": "EUR",
        "currencyTo": "GBP",
        "amountSell": sell,
        "amountBuy": sell*rate,
        "rate": rate,
        "timePlaced" : d.toString(),
        "originatingCountry" : randomCountry()
    });
},300);
