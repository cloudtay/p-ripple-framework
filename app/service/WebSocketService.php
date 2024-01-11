<?php

namespace app\service;

use Cclilshy\PRippleProtocolWebsocket\WebSocket;
use Worker\Built\JsonRpc\Attribute\RPC;
use Worker\Built\JsonRpc\JsonRpc;
use Worker\Socket\TCPConnection;
use Worker\Worker;

class WebSocketService extends Worker
{
    use JsonRpc;

    public int $mode = Worker::MODE_INDEPENDENT;

    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->protocol(WebSocket::class);
        $this->bind('tcp://0.0.0.0:8001', [SO_REUSEADDR => 1, SO_REUSEPORT => 1]);
    }

    /**
     * @param string        $context
     * @param TCPConnection $tcpConnection
     * @return void
     */
    public function onMessage(string $context, TCPConnection $tcpConnection): void
    {
        $tcpConnection->send("message: {$context}");
    }

    /**
     * @param TCPConnection $tcpConnection
     * @return void
     */
    public function onHandshake(TCPConnection $tcpConnection): void
    {
        $tcpConnection->send("Hello world!");
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
