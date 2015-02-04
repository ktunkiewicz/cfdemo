/**
 * @file This is the REST endpoint test using frisby.js.
 * @author Kamil Tunkiewicz <kamil.tunkiewicz@gmail.com>
 * @license MIT
 */

var frisby = require('frisby');
var config = require('../config');

frisby.create('GET test')
    .get('http://' + config.host + ':' + config.port)
    .expectStatus(200)
    .expectHeaderContains('content-type', 'application/json')
    .expectJSON({'OK':1})
.toss();

frisby.create('POST test - valid data')
    .post('http://' + config.host + ':' + config.port, {
        "userId": "0",
        "currencyFrom": "EUR",
        "currencyTo": "GBP",
        "amountSell": 1000,
        "amountBuy": 747.10,
        "rate": 0.7471,
        "timePlaced" : "14-JAN-15 10:27:44",
        "originatingCountry" : "FR"
    }, {json: true})
    .expectStatus(200)
    .expectHeaderContains('content-type', 'application/json')
    .expectJSON({'OK':1})
.toss();

frisby.create('POST test - invalid types but it will be casted to valid data')
    .post('http://' + config.host + ':' + config.port, {
        "userId": "0",          // not tested
        "currencyFrom": "EUR",  // not tested
        "currencyTo": "GBP",    // not tested
        "amountSell": "1000",   // as string not float
        "amountBuy": "747.10",  // as string not float
        "rate": "0.7471",       // as string not float
        "timePlaced" : "14-JAN-15 10:27:44", // not tested
        "originatingCountry" : "FR"          // not tested 
    }, {json: true})
    .expectStatus(200)
    .expectHeaderContains('content-type', 'application/json')
    .expectJSON({'OK':1})
.toss();


frisby.create('POST test - empty data')
    .post('http://' + config.host + ':' + config.port, {
        'dummy': 1
    }, {json: true})
    .expectStatus(200)
    .expectHeaderContains('content-type', 'application/json')
    .expectJSON({
        'OK':0,
        'errors': [
            'userId cannot be empty',
            'currencyFrom cannot be empty',
            'currencyTo cannot be empty',
            'amountSell cannot be empty',
            'amountBuy cannot be empty',
            'rate cannot be empty',
            'timePlaced cannot be empty',
            'originatingCountry cannot be empty'
        ]
    })
.toss();


frisby.create('POST test - wrong data')
    .post('http://' + config.host + ':' + config.port, {
        "userId": "ABCD",
        "currencyFrom": "A12",
        "currencyTo": "B34",
        "amountSell": "ABC",
        "amountBuy": "DEF",
        "rate": "GHI",
        "timePlaced" : "qwerty",
        "originatingCountry" : "12"
    }, {json: true})
    .expectStatus(200)
    .expectHeaderContains('content-type', 'application/json')
    .expectJSON({
        'OK':0,
        'errors': [
            'userId is not numeric',
            'currencyFrom is incorrect',
            'currencyTo is incorrect',
            'amountSell is incorrect',
            'amountBuy is incorrect',
            'rate is incorrect',
            'timePlaced is not valid date',
            'originatingCountry is incorrect'
        ]
    })
.toss();

frisby.create('POST test - wrong data 2')
    .post('http://' + config.host + ':' + config.port, {
        "userId": false,
        "currencyFrom": 123,
        "currencyTo": 321,
        "amountSell": true,
        "amountBuy": false,
        "rate": true,
        "timePlaced" : false,
        "originatingCountry" : 12
    }, {json: true})
    .expectStatus(200)
    .expectHeaderContains('content-type', 'application/json')
    .expectJSON({
        'OK':0,
        'errors': [
            'userId cannot be empty',
            'currencyFrom is incorrect',
            'currencyTo is incorrect',
            'amountSell is incorrect',
            'amountBuy cannot be empty',
            'rate is incorrect',
            'timePlaced cannot be empty',
            'originatingCountry is incorrect'
        ]
    })
.toss();

var nextYear = parseInt(new Date().getFullYear().toString().substr(2))+1;

frisby.create('POST test - wrong data length')
    .post('http://' + config.host + ':' + config.port, {
        "userId": "134256",     // not tested
        "currencyFrom": "EURO", // must be 3 chars
        "currencyTo": "GB",     // must be 3 chars
        "amountSell": "-1000",  // must be greater than 0
        "amountBuy": "0",       // must be greater than 0
        "rate": "-0.0001",      // must be greater than 0
        "timePlaced" : "14-JAN-" + nextYear + " 10:27:44", // must not be in future
        "originatingCountry" : "F"           // must be 3 chars
    }, {json: true})
    .expectStatus(200)
    .expectHeaderContains('content-type', 'application/json')
    .expectJSON({
        'OK':0,
        'errors': [
            'currencyFrom has incorrect length',
            'currencyTo has incorrect length',
            'amountSell must be greater than zero',
            'amountBuy must be greater than zero',
            'rate must be greater than zero',
            'timePlaced date cannot be in future',
            'originatingCountry has incorrect length'
        ]
    })
.toss();

var test = frisby.create('CHECK DATABASE entries')
    .get('http://' + config.host + ':' + config.port)
    .after(function(err,res,body){
        var mongo = require('mongodb');
        var monk = require('monk');
        var db = monk(
            config.dbHost +':' +
            config.dbPort + '/' +
            config.dbDatabase
        );
        var data = db.get('messages');
        var found = data.find({ 'userId': 0 }, function (err) {
            if (err) {
                console.log("!!! ERROR finding test datbase entries !!!");
                console.log(err);
            }
        });
        found.on('complete', function(err, doc){
            if (doc.length!==2) {
                console.log("!!! Wrong database content !!!");
            }
            data.remove({'userId': 0 }, function (err) {
                if (err) {
                    console.log("!!! ERROR removing test datbase entries !!!");
                    console.log(err);
                }
                db.close();
            });
        });
    }).toss();