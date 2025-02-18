<?php

namespace App\Helpers\Irc\Bot\Client;

use App\Helpers\Irc\Bot\Client\BaseHelper;
use Cache;
use Config;
use Event;

final class Command
{
    protected $message;
    protected $name;
    protected $sender;
    protected $params;
    protected $text;
    protected $time;

    /**
     * Create a new command object
     *
     * @param  Message $message
     */
    public function __construct(Message $message)
    {
        // Keep the original message
        $this->message = $message;
        // Keep the message sender handy
        $this->sender  = $message->sender;
        // Split the command name and params out
        list($this->name, $params) = explode(' ', $message->body, 2) + array("", "");

        if (substr($params, 0, 1) == ':') {
            $params = array(substr($params, 1));

        } elseif (($p = strpos($params, ' :')) !== false) {
            $text = substr($params, $p+2);
            $params = explode(' ', substr($params, 0, $p));
            $params[] = $text;

        } else {
            $params = explode(' ', $params);
        }
        $this->params = $params;
        $this->text = implode(' ', $params);
        $this->time = microtime(true);
    }

    /**
     * Create a new command
     *
     * @param  Message $message
     * @return Command
     */
    public static function make(Message $message)
    {
        return new static($message);
    }

    /**
     * Register a new command
     *
     * @param  string $name
     * @param  Closure $closure
     */
    public static function register($name, $closure, $priority = 999)
    {
        Event::listen('taylor::command: '.strtolower($name), $closure, $priority);

        // register the command into the cache
        BaseHelper::addToCache('taylor.functions', $name);
    }

    /**
     * Run the command
     */
    public function run()
    {

        if (isset($this->sender)) {
            if (BaseHelper::testForBot($this->sender->nick)) {
                return [];
            }
        }

        $response = Event::dispatch('taylor::command: '.strtolower($this->name), array($this));
        if (empty($response)) {
            return [];
        }

        return $response;
    }

    /**
     * Magic Getter
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'name':
            case 'sender':
            case 'params':
            case 'message':
            case 'text':
            case 'time':
                return $this->$name;
            break;
        }
    }

    /**
     * Magic Setter
     *
     * @param  string $name
     * @param  mixed  $value
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'name':
            case 'sender':
            case 'params':
            case 'message':
            case 'text':
            case 'time':
                ; // Read-only
            break;
        }
    }

    /**
     * Magic Is Set Check
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->$name);
    }

    /**
     * Magic Unsetter
     *
     * @param  string $name
     */
    public function __unset($name)
    {
        switch ($name) {
            case 'name':
            case 'sender':
            case 'params':
            case 'message':
            case 'text':
            case 'time':
                ; // Read-only
            break;
        }
    }

}
