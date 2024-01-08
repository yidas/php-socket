<?php

namespace yidas\socket;

use yidas\socket\exception\ConnectException as Exception;

/**
 * Socket
 * 
 * @author  Nick Tsai <myintaer@gmail.com>
 * @version 0.1
 */
class Socket
{
    /**
     * PHP Socket resource
     */
    protected $socket;

    /**
     * Default config
     */
    protected $defaultConfig = [
        'timeout' => 15,
    ];

    /**
     * Construction
     */
    public function __construct(int $domain = AF_INET, int $type = SOCK_STREAM, int $protocol = 0, $config = []) {
        
        $this->defaultConfig = ($config) ? array_merge($this->defaultConfig, $config) : $this->defaultConfig;
        $this->socket_create($domain, $type, $protocol);
        
        // return $this;
    }

    /**
     * Create alias
     * 
     * @return self
     */
    public function create(int $domain, int $type, int $protocol) {
        
        $this->socket_create($domain, $type, $protocol);

        return $this;
    }

    /**
     * Socket create
     * 
     * @return object PHP Socket resource
     */
    public function socket_create(int $domain, int $type, int $protocol) {
        
        if ($this->socket) {
            
            $this->close();
        }
        
        return $this->socket = socket_create($domain, $type, $protocol);
    }

    /**
     * Socket connect
     */
    public function connect(string $address, int $port = null, $config = []) {

        $config = array_merge($this->defaultConfig, $config);

        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $config['timeout'], 'usec' => 0]);
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $config['timeout'], 'usec' => 0]);

        return @socket_connect($this->socket, $address, $port);
    }

    /**
     * Socket read
     * 
     * @return string
     */
    public function read($length = 1024, $inLoop = true) {
     
        if (!$inLoop) {
            
            return socket_read($this->socket, $length);
        }

        $response = "";
        while (true) {
            
            $result = socket_recv($this->socket, $buffer, $length, 0);
            
            if ($result === false) {
                // $errorCode = socket_last_error($this->socket);
                // $errorMessage = socket_strerror($errorCode);
                // throw new Exception($errorMessage, $errorCode);
                break;
            }

            $response .= $buffer;

            if ($result === 0 || $result < $length) {
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
            $result = socket_write($this->socket, $string, $length);

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
    public function getSocket() {
        
        return $this->socket;
    }

    /** 
     * Socket close
     * 
     * @return void
     */
    public function close() {
     
        return socket_close($this->socket);
    }

    /**
     * Magic call for PHP native function
     */
    public function __call(string $function, array $arguments) {
        
        if (!is_callable($function)) {

            throw new \Exception("There is no relevant function: {$function}()", 400);
        }

        array_unshift($arguments, $this->socket);

        return call_user_func_array($function, $arguments);
    }
}