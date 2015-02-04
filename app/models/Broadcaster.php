<?php

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Broadcaster implements WampServerInterface {

    protected $subscribedTopics = array();

    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
	unset($this->subscribedTopics[$topic->getId()]);
    }
    public function onOpen(ConnectionInterface $conn) {
    }
    public function onClose(ConnectionInterface $conn) {
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    /**
     * @param string JSON string received from ZeroMQ
     */
    public function onNewEntry($entry) {
        $entryData = json_decode($entry, true);
        
        $channels = $entryData['timespans']['minute']['rateTypes'];
        foreach($channels as $name => $channel) {
            // If the lookup topic object isn't set there is no one to publish to
            if (!array_key_exists($name, $this->subscribedTopics)) {
                continue;
            }                                           
            $topic = $this->subscribedTopics[$name];
            // send the data to all the clients subscribed to that category
            $topic->broadcast($channel);
        }
                                                    
    }

}
