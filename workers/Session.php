<?php

namespace hoverboard\workers;

class Session
{
    protected static $keys = array();

    public static function status()
    {
        // For future compatibility with PHP 5.4 session_status
        // $status = false;
        // if (function_exists("session_status")) {
        //     $session_status = session_status();
        //     if ($session_status === "PHP_SESSION_ACTIVE") {
        //         $status = true;
        //     }
        //     return $status;
        // }

        $session_id = session_id();
        return !empty($session_id);
    }


    public static function start()
    {
        if (!static::status()) {
            session_start();
        }
    }


    public static function set($key, $value)
    {
        static::start();
        $_SESSION[$key] = $value;
        return true;
    }


    public static function push($key, array $values)
    {
        static::start();
        $session_value = static::get($key);

        if (!$session_value) {
            return static::set($key, $values);
        }

        if (!is_array($session_value)) {
            // Throw exception: "can't push to non-array"
            return false;
        }

        static::set($key, array_merge_recursive($session_value, $values));
    }


    public static function get($key = null)
    {
        static::start();

        if (is_null($key)) {
            return $_SESSION;
        }
        return static::keyExists($key) ? $_SESSION[$key] : false;
    }


    public static function keyExists($key)
    {
        static::start();
        return isset($_SESSION[$key]);
    }


    public static function delete($key)
    {
        static::start();
        if (static::keyExists($key)) {
            unset($_SESSION[$key]);
        }
    }


    public static function clear()
    {
        static::start();
        $_SESSION = array();
    }


    public static function destroy()
    {
        static::clear();
        session_destroy();
    }


    public static function array_smash($a1, $a2)
    {
        $merged = array();

        foreach ($a1 as $key => $item) {
            if (is_numeric($key)) {
                echo "NUMBER FOUND : " . $key;
            }
        }

        return $merged;
    }
}