***php*** Socket
================

Modern PHP Socket class based on native infra (pure PHP, CI, Yii, Laravel support)

[![Latest Stable Version](https://poser.pugx.org/yidas/socket/v/stable?format=flat-square)](https://packagist.org/packages/yidas/socket)
[![License](https://poser.pugx.org/yidas/socket/license?format=flat-square)](https://packagist.org/packages/yidas/socket)

DEMONSTRATION
-------------

### Client

```php
try {

    $socket = new \yidas\socket\Client([
        'protocol' => 'tcp',
        'host' => 'smtp.your.com',
        'port' => '25',
    ]);

} catch (Exception $e) {
    
    die("Failed to connect: {$e->getMessage()} (Code: {$e->getCode()})");
}

echo $socket->read();
// ...
$socket->write('STARTTLS');
echo $socket->read();
$result = $socket->enableCrypto();
// ...

$socket->close();
```

Native function support:

```php
$socket = new \yidas\socket\Client();

try {

  $socket->stream_socket_client('smtp.your.com:25', $errorCode, $errorMsg, 15);

} catch (Exception $e) {
    
    die("Failed to connect: {$e->getMessage()} (Code: {$e->getCode()})");
}

$socket->fread(1024);
// ...
$socket->fwrite('STARTTLS');
echo $socket->fread(1024);
$result = $socket->stream_socket_enable_crypto(true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
// ...

$socket->fclose();
```
