<?php

namespace App\Helpers\Irc\Bot\Client;

use Config;
use Illuminate\Support\Arr;

final class Sender
{

    private $string;
    private $nick;
    private $user;
    private $host;
    private $server;

    /**
     * Creates a new, immutable Sender object
     *
     * @param string $string The sender string.
     * @param array  $parts  The sender parts.
     */
    private function __construct($string, array $parts)
    {
        $this->string = $string;

        $this->nick = Arr::get($parts, 'nick', null);
        $this->user = Arr::get($parts, 'user', null);
        $this->host = Arr::get($parts, 'host', null);
        $this->server = Arr::get($parts, 'server', null);
    }

    /**
     * Creates a new, immutable Sender object from the given prefix string
     *
     * @param  string  $string
     * @return IRC\Sender
     */
    public static function parse($string)
    {
        // prefix     =  servername / ( nickname [ [ "!" user ] "@" host ] )
        // servername =  hostname
        // host       =  hostname / hostaddr
        // hostname   =  shortname *( "." shortname )
        // shortname  =  ( letter / digit ) *( letter / digit / "-" )
        //                 *( letter / digit )
        // nickname   =  ( letter / special ) *8( letter / digit / special / "-" )
        // user       =  1*( %x01-09 / %x0B-0C / %x0E-1F / %x21-3F / %x41-FF )
        //                 ; any octet except NUL, CR, LF, " " and "@"

        if (preg_match("/^:?(?P<nick>[a-zA-Z\\[\\]\\\\`{}_-][a-zA-Z0-9\\[\\]\\\\`{}_-]+)(?:!(?P<user>[^ @]+))?(?:@(?P<host>[~a-zA-Z0-9-].+))?$/u", $string, $m)) {
            return new Sender($string, $m);
        } elseif (preg_match("/^:?(?P<nick>[a-zA-Z\\[\\]\\\\`{}_-][a-zA-Z0-9\\[\\]\\\\`{}_-]+)(?:@(?P<host>[~a-zA-Z0-9-].+))?(?:!(?P<user>[^ @]+))?$/u", $string, $m)) {
            return new Sender($string, $m);
        } else {
            return new Sender($string, array('server' => substr($string, 1)));
        }
    }

    /**
     * Creates a new, immutable Sender object representing a server.
     *
     * @param string $server The servername of the sender.
     * @return IRC\Sender
     */
    public static function makeServer($server)
    {
        return new Sender($server, compact('server'));
    }

    /**
     * Creates a new, immutable Sender object representing a user.
     *
     * @param string $nick The nickname of the sender.
     * @param string $user The username of the sender.
     * @param string $host The hostname of the sender.
     * @return IRC\Sender
     */
    public static function makeUser($nick, $user = '', $host = '')
    {
        $string = $nick . ($user ? "@$user" : '') . ($host ? "!$host" : '');
        return new Sender($string, compact('nick', 'user', 'host'));
    }

    /**
     * Is this Sender a user?
     *
     * @return bool TRUE if this Sender is a user.
     */
    public function isUser()
    {
        return !is_null($this->nick);
    }

    /**
     * Is this Sender a server?
     *
     * @return bool TRUE if this Sender is a server.
     */
    public function isServer()
    {
        return !is_null($this->server);
    }

    /**
     * Get the string, nick, user, host or server from this Sender.
     *
     * @param string $name One of string, nick, user, host or server
     * @return string The value of $name
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * Check that the string, nick, user, host or server are present.
     *
     * @param string $name One of string, nick, user, host or server
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->$name) and ! empty($this->$name);
    }

    /**
     * Sender objects are immutable, their values may not be changed.
     *
     * @param string
     * @param mixed
     * @internal
     */
    final public function __set($name, $value)
    {
    }

    /**
     * Sender objects are immutable, their values may not be unset.
     *
     * @param string
     * @internal
     */
    final public function __unset($name)
    {
    }

}
