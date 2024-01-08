<?php

namespace yidas\socket;

use yidas\socket\exception\ConnectException as Exception;

/**
 * Socket
 * 
 * @author  Nick Tsai <myintaer@gmail.com>
 * @version 0.1
 */
class Client
{
    /**
     * PHP stream resource
     */
    protected $stream;

    /**
     * Default config
     */
    protected $defaultConfig = [
        'protocol' => '',
        'host' => '',
        'port' => '',
        'timeout' => 15,
    ];

    /**
     * Construction
     */
    public function __construct($config = []) {
        
        if ($config) {
            
            $this->connect($config);
        }

        // return $this;
    }

    /**
     * Stream socket connect
     * 
     * @return object Resource
     */
    public function connect($config) {
        
        $config = array_merge($this->defaultConfig, $config);

        $protocol = ($config['protocol']) ? "{$config['protocol']}://" : '';
        $address = "{$protocol}{$config['host']}:{$config['port']}";

        return $this->stream_socket_client($address, $errorCode, $errorMsg, $config['timeout']);
    }

    /**
     * Stream socket connect native parameter
     * 
     * @return object Resource
     */
    public function stream_socket_client(string $address, int &$errorCode = null, string &$errorMsg = null, float $timeout = null, int $flags = STREAM_CLIENT_CONNECT, $context = null) {

        if ($this->stream) {

            $this->close();
        }

        $context = ($context) ? $context : stream_context_create();
        $result = $this->stream = @stream_socket_client($address, $errorCode, $errorMsg, $timeout, $flags, $context);

        if ($result === false) {

            throw new Exception("{$errorMsg} (Address: {$address})", $errorCode);
        }

        return $result;
    }

    /**
     * Socket read
     * 
     * @return string
     */
    public function read($length = 1024, $inLoop = true) {
     
        if (!$inLoop) {
            
            return fread($this->stream, $length);
        }

        $response = "";
        while (true) {
            
            $result = fread($this->stream, $length);
            
            if ($result === false) {
                // $errorCode = socket_last_error($this->stream);
                // $errorMessage = socket_strerror($errorCode);
                // throw new Exception($errorMessage, $errorCode);
                break;
            }

            $response .= $result;

            if (strlen($result) < $length) {
                break;
            }
        }
        
        return $response;
    }

    /**
     * Socket write
     * 
     * @return boolean
     */
    public function write(String $string, $length = 1024, $inLoop = true) {
        
        while (true) {
            
            $length = strlen($string);
            $result = fwrite($this->stream, $string, $length);

            // All done or failed
            if ($result >= $length) {

                return true;
            }
            elseif ($result === false || !$inLoop) {

                return false;
            }

            // Continue from last byte
            $string = substr($string, $result);
        }
    }

    /**
     * Get PHP socket resource
     * 
     * @return object PHP Socket resource
     */
    public function getResource() {
        
        return $this->stream;
    }

    /**
     * Enable crypto
     * 
     * @return boolean
     */
    public function enableCrypto(bool $enable = true, int $cryptoMethod = null) {
        
        if (!$cryptoMethod) {
            $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
            }
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            }
        }
        
        return stream_socket_enable_crypto($this->stream, $enable, $cryptoMethod);
    }

    /** 
     * Socket close
     * 
     * @return void
     */
    public function close() {
        
        if ($this->stream) {

            fclose($this->stream);
        }

        return true;
    }

    /**
     * Magic call for PHP native function
     */
    public function __call(string $function, array $arguments) {
        
        if (!is_callable($function)) {

            throw new \Exception("There is no relevant function: {$function}()", 400);
        }

        array_unshift($arguments, $this->stream);

        return call_user_func_array($function, $arguments);
    }
}
