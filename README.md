# Demo project for CurrencyFair #

To CF manager: **Please notice** that there is a script running and adding POST messages to the endpoint all the time so you can se how data changes on frontend. The script generated random data and post it to endpoint (source: ./endpoint/endpoint_feeder.sh).
Please email me if you want me to turn it off.


## Overview ##

My solution is aimed on **performance, reliability and scalability**.
The test enviroment used is only **1-core 1GB RAM** VPS (the cheapest one from OVH) and it handles about **400-500** simultaneous connections to the REST endpoint (processing power seems to be a limit here). Of course with such limited resources the endpoint response time will be lower with more clients connected to frontend/sockets.

So let's see how I did it.

The project consist of:
- REST endpoint
- Data processor + Data broadcaster (sockets server)
- Frontend (sockets client)

I splited data processor and broadcaster into two separate processes for better performance and responsivity.

Below I will briefly describe each component and technologies used in this project.

## REST endpoint ##

This part is made in node.js with MongoDB database.
**Standalone Node.js HTTP server process** listens on separate port (because I only have one domain, in production this can be moved into other domain/physical server.) This part is completly separate from others for better scalability.
The script **validates** data providing nice feedback on errors, then puts **sanitized** data into MongoDB.
Full tests included. Tests are made using firsby.js.

## Data Processor ##

All next modules are made in **PHP with Laravel** framework behind. 
The Data Processor is an artisan module (shortly, Artisan is a CLI version of Laravel) placed inside Laravel project structure but executed as a **separate process**. 
The process repetitively fetch last 1 minute of messages incoming to REST endpoint and process it to calculate:
- minimal, maximal and average currency rate; total and average sell amount; total count of operations
- the above data are calculated per country for each currency combinations
- the output is a JSON tree od data to be easily distributed and processed by frontend
The Data Processor connects **through internal socket (React/ZMQ)** with Data Broadcaster.

## Data Broadcaster ##

This is also an artisan module run as **separate process** listnening on separate port for WAMP protocol connections from frontned clients. It is based on **PHP, Laravel and Ratchet (socketo.me)**. 
The broadcaster simply repeats Data Processor messages to each client connected to its socket.

## Frontend ##

The frontend is based on Laravel (framework), autobahn.js (sockets) and D3 (rendering).

# Installation #

If you want to install this software you will need:
- Centos 7 or other RHEL-like distro
- Node.js
- npm
- mongoDB
- php modules: php_mongo php-mcrypt php-pecl-zmq
- webserwer configured to /public directory of Laravel.

Other dependencies should be automatically installed via composer and npm.


