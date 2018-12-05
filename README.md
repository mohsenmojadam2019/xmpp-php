# PHP library for XMPP

This is low level socket implementation for enabling PHP to 
communicate with XMPP due to lack of such libraries online (at least ones I 
could find). 

Current version is oriented towards simplicity and XMPP understanding under the
hood. Should the need arise, I will expand the library, and by all means feel
free to contribute to the repository. 

# Install and example

After initial `composer install`, the library is ready to go.

You can see usage example in `Example.php` file by changing credentials to 
point to your XMPP server and from project root run `php src/Example.php`.

# Library usage
## Init
In order to start using the library you first need to instantiate a new `Connector` 
class. Everything except setting a port number is required. If omitted, port 
will default to `5222` which is XMPP default. 

```
$connector = new Connector();

$connector
    ->setHost($host)
    ->setPort($port)
    ->setUsername($username)
    ->setPassword($password);
```

Connector object is required for establishing the connection and every other subsequent
request, so once set it should not be changed. 

Once this is set you can instantiate new client object and pass the connector object in.

## Connect & auth
```
$client = new XmppClient();
$client->connect($connector);
```

`$client->connect()` method does a few things:
1. Connects to the socket which was initialized in `XmppClient` constructor
2. Opens XML stream to exchange with XMPP server
3. Tries to authorize with the server based on provided credentials

Current version supports only `PLAIN` auth method. 

## Setting resource

`$client->setResource()` method sets the resource (duh!). This will be extended in future releases
to support getting resource based on `JID/resource` parsing, but for now you can set it
yourself by passing it as a parameter.

## Sending messages

`$client->sendMessage()` takes 3 parameters of which the last one is optional. First parameter
is the actual message you'd like to send, second one is recipient of the message and third
one is type of message to be sent. This is currently set to default to `CHAT`, but will probably
be extended in future releases

## Getting something back

Mostly all methods look as if they do nothing unless you get some output back. For this you can 
run a `$client->getServerResponse()` method which will fetch the XML from server back.
