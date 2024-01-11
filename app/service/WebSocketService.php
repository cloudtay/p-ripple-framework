<?php

namespace app\service;

use Worker\Built\JsonRpc\Attribute\RPC;
use Worker\Socket\TCPConnection;
use Worker\Worker;

class WebSocket extends Worker
{
    public int $mode = Worker::MODE_INDEPENDENT;

    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->protocol(\Cclilshy\PRippleProtocolWebsocket\WebSocket::class);
        $this->bind('tcp://0.0.0.0:8001');
    }

    /**
     * @param string        $context
     * @param TCPConnection $client
     * @return void
     */
    public function onMessage(string $context, TCPConnection $client): void
    {
        $client->send("message: {$context}");
    }

    /**
     * @param TCPConnection $client
     * @return void
     */
    public function onHandshake(TCPConnection $client): void
    {
        $client->send("Hello world!");
    }

    /**
     * @param string $message
     * @return void
     */
    #[RPC("sendMessageToAll")] public function sendMessageToAll(string $message): void
    {
        foreach ($this->getClients() as $client) {
            $client->send($message);
        }
    }
}
