<?php

namespace Norgul\Xmpp;

use Exception;
use Norgul\Xmpp\Authorization\Plain;

/**
 * Class Socket
 * @package Norgul\Xmpp
 */
class XmppClient
{
    /**
     * Global socket instance
     */
    protected $socket;

    /**
     * XmppClient constructor. Initializing a new socket
     */
    public function __construct()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
        echo __METHOD__ . " Socket created\n";
    }

    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * Open socket to host:port and authorize with given credentials
     *
     * @param Connector $connector
     */
    public function connect(Connector $connector)
    {
        $result = socket_connect($this->socket, $connector->getHost(), $connector->getPort());
        echo $result ? "Socket connected\n" : "Socket connection failed. $result " . socket_strerror(socket_last_error($this->socket)) . "\n";

        /**
         * Opening stream to XMPP server
         */
        $this->send(Xml::OPEN_TAG);

        $this->authorize($connector->getUsername(), $connector->getPassword());
    }

    /**
     * Sending XML stanzas to open socket
     * @param $xml
     */
    public function send($xml)
    {
        socket_write($this->socket, $xml, strlen($xml));
    }

    /**
     * Send message to Jabber user
     * TODO: refactor to use object for type instead of string
     *
     * @param $message
     * @param $to
     * @param $type
     */
    public function sendMessage($message, $to, $type = "CHAT")
    {
        $preparedString = str_replace(
            ['{message}', '{to}', '{type}'],
            [Xml::quote($message), Xml::quote($to), Xml::quote($type)],
            Xml::MESSAGE
        );

        $this->send($preparedString);
    }

    /**
     * Set resource
     * TODO: this should be refactored to get resources either automatically or with JID/resource
     * @param $resource
     */
    public function setResource($resource)
    {
        $preparedString = str_replace('{resource}', Xml::quote($resource), Xml::RESOURCE);
        $this->send($preparedString);
    }

    public function authorize($username, $password)
    {
        $preparedString = Auth::authorize(new Plain(), $username, $password);
        $this->send($preparedString);
    }

    /**
     * Get response from server if any
     */
    public function getServerResponse()
    {
        // Wait max 3 seconds before terminating the socket
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
        try {

            echo "*** Data ***\n\n";
            while ($out = socket_read($this->socket, 2048)) {
                echo str_replace("><", ">\n<", $out) . "\n\n";
            }
            echo "\n\n************\n";

        } catch (Exception $e) {
            echo "Error\n";
            echo $e;
        }

        // Reset waiting period to infinite
        //socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 0, "usec" => 0));
    }

    /**
     * End the XMPP session and close the socket
     */
    public function disconnect()
    {
        $this->send(Xml::CLOSE_TAG);
        socket_close($this->socket);
    }
}