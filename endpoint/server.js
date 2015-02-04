/**
 * @file This is a fast, tiny endpoint accepting POST messages and sending them do MongoDB.
 * @author Kamil Tunkiewicz <kamil.tunkiewicz@gmail.com>
 * @license MIT
 */
var stats = require('measured').createCollection();
var bodyParser = require('body-parser');
var validator = require('validator');
var express = require('express');
var config = require('./config');
var mongo = require('mongodb');
var monk = require('monk');
var app = express();

var db = monk(
    config.dbHost +':' +
    config.dbPort + '/' +
    config.dbDatabase
);
var messages = db.get('messages');

/**
 * Incoming data validation
 * 
 * @param {object} data
 * @returns {json}
 */
var validate = function(data) {
    var err = [];
    
    if (!data.userId)                                               { err.push("userId cannot be empty"); }
    else if (!validator.isNumeric(data.userId))                     { err.push("userId is not numeric"); }
    if (!data.currencyFrom)                                         { err.push("currencyFrom cannot be empty"); }
    else if (!validator.isAlpha(data.currencyFrom))                 { err.push("currencyFrom is incorrect"); }
    else if (data.currencyFrom.toString().length!==3)               { err.push("currencyFrom has incorrect length"); }
    if (!data.currencyTo)                                           { err.push("currencyTo cannot be empty"); }
    else if (!validator.isAlpha(data.currencyTo))                   { err.push("currencyTo is incorrect"); }
    else if (data.currencyTo.toString().length!==3)                 { err.push("currencyTo has incorrect length"); }
    if (!data.amountSell)                                           { err.push("amountSell cannot be empty"); }
    else if (!validator.isFloat(data.amountSell))                   { err.push("amountSell is incorrect"); }
    else if (validator.toFloat(data.amountSell)<=0)                 { err.push("amountSell must be greater than zero"); }
    if (!data.amountBuy)                                            { err.push("amountBuy cannot be empty"); }
    else if (!validator.isFloat(data.amountBuy))                    { err.push("amountBuy is incorrect"); }
    else if (validator.toFloat(data.amountBuy)<=0)                  { err.push("amountBuy must be greater than zero"); }
    if (!data.rate)                                                 { err.push("rate cannot be empty"); }
    else if (!validator.isFloat(data.rate))                         { err.push("rate is incorrect"); }
    else if (validator.toFloat(data.rate)<=0)                       { err.push("rate must be greater than zero"); }
    if (!data.timePlaced)                                           { err.push("timePlaced cannot be empty"); }
    else if (!validator.isDate(data.timePlaced))                    { err.push("timePlaced is not valid date"); }
    else if (validator.toDate(data.timePlaced) > new Date())        { err.push("timePlaced date cannot be in future"); }
    if (!data.originatingCountry)                                   { err.push("originatingCountry cannot be empty"); }
    else if (!validator.isAlpha(data.originatingCountry))           { err.push("originatingCountry is incorrect"); }
    else if (data.originatingCountry.toString().length!==2)         { err.push("originatingCountry has incorrect length"); }
    
    if (err.length) {
        return { 'OK': 0, 'errors': err };
    } else {
        return { 'OK': 1 };
    }
};

/**
 * Sanitizes and converts input data
 * @param {json} data
 * @returns {json}
 */
var sanitize = function(data) {
    var ret = {};
    ret.userId          = validator.toInt(data.userId);
    ret.currencyFrom    = validator.trim(data.currencyFrom);
    ret.currencyTo      = validator.trim(data.currencyTo);
    ret.amountSell      = validator.toFloat(data.amountSell);
    ret.amountBuy       = validator.toFloat(data.amountSell);
    ret.rate            = validator.toFloat(data.rate);
    ret.timePlaced          = validator.toDate(data.timePlaced);
    ret.originatingCountry  = validator.toDate(data.originatingCountry);
    ret.originatingCountry  = validator.trim(data.originatingCountry);
    return ret;
};

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

/*
* Performance logging to stdout
*/
if (!process.argv[2] || process.argv[2]!=="--quiet" ) {
    setInterval(function() {
        console.log('\033[2J');
        console.log(stats.toJSON());
    }, 1000);
}


/*
 * =========== ROUTES ===========
 */

/**
 * Returns status message on GET methoed
 */
app.get('/', function(req, res) {
    res.send({ 'OK': 1 });
});

/**
 * Puts trade messages into database
 */
app.post('/', function (req, res) {
    stats.meter('requestsPerSecond').mark();
    
    // Validate data
    var check = validate(req.body);
    
    if (check.OK) {
        
        // Sending to database
        messages.insert(
            sanitize(req.body),
            function (err, doc) {
                if (err) {
                    res.send({ 'OK': 0, 'error': err });
                } else {
                    res.send({ 'OK': 1 });
                }
        });      
        
    } else {
        res.send(check);
    }
    
});

app.listen(config.port,config.host);

console.log('REST endpoint running at http://'+config.host+':'+config.port);
