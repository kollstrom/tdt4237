<?php
/**
 * Created by PhpStorm.
 * User: mumie
 * Date: 12.03.2018
 * Time: 19.37
 */

namespace App\System;


class CSRF
{
    function storeInSession($key,$value)
    {
        if (isset($_SESSION))
        {
            $_SESSION[$key]=$value;
        }
    }
    function unsetSession($key)
    {
        $_SESSION[$key]=' ';
        unset($_SESSION[$key]);
    }
    function getFromSession($key)
    {
        if (isset($_SESSION[$key]))
        {
            return $_SESSION[$key];
        }
        else {  return false; }
    }

    function generateToken($unique_form_name)
    {
        $token = hash('sha256', $unique_form_name . random_bytes(32)); // PHP 7, or via paragonie/random_compat
        $this->storeInSession($unique_form_name,$token);
        return $token;
    }

    function validateToken($unique_form_name,$token_value)
    {
        $token = $this->getFromSession($unique_form_name);
        if (!is_string($token_value)) {
            return false;
        }
        $result = hash_equals($token, $token_value);
        $this->unsetSession($unique_form_name);
        return $result;
    }

}