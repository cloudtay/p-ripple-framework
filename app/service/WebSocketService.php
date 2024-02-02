<?php

namespace app\service;

use Cclilshy\PRipple\Worker\Built\JsonRPC\Attribute\RPCMethod;
use Cclilshy\PRipple\Worker\Built\JsonRPC\JsonRPC;
use Cclilshy\PRipple\Worker\Socket\TCPConnection;
use Cclilshy\PRipple\Worker\Worker;
use Cclilshy\PRipple\Worker\WorkerNet;
use Cclilshy\PRippleProtocolWebsocket\WebSocket;

class WebSocketService extends WorkerNet
{
    use JsonRPC;

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
    #[RPCMethod("sendMessageToAll")] public function sendMessageToAll(string $message): void
    {
        foreach ($this->getClients() as $client) {
            $client->send($message);
        }
    }
}
