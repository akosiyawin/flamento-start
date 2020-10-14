<?php
/**
 * Class Session
 * @package app\core
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core;

use app\core\exception\ForbiddenException;

class Session
{
    private const FLASH_KEY = 'flash_messages';

    public function __construct()
    {
        session_start();

        if($_ENV['CSRF'] === "on")
            $this->makeToken();

        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashMessages as $key => &$flashMessage) {
            $flashMessage['remove'] = true; //marked
        }
        $_SESSION[self::FLASH_KEY] = $flashMessages;

    }

    public function setFlash(string $key, string $message)
    {
        $_SESSION[self::FLASH_KEY][$key] = [
            'remove' => false,
            'value' => $message
        ];
    }

    public function getFlash(string $key)
    {
        return $_SESSION[self::FLASH_KEY][$key]['value'] ?? false;
    }

    public function set($key,$value)
    {
        $_SESSION[$key] = $value;
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public function get($key)
    {
        return $_SESSION[$key] ?? false;
    }

    public function __destruct()
    {
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashMessages as $key => $flashMessage)
        {
            if($flashMessage['remove'])
            {
                unset($flashMessages[$key]);
            }
        }

        $_SESSION[self::FLASH_KEY] = $flashMessages;
    }

    //TODO:: Per-form token
    private function makeToken()
    {
        if (!$this->get("csrf_token"))
            $this->set("csrf_token",bin2hex(random_bytes(32)));
    }

    public function verifyToken($token)
    {
        if ($this->get("csrf_token"))
        {
            if (!hash_equals($this->get("csrf_token"), $token))
            {
                // Proceed to process the form data
                throw new ForbiddenException("WARNING! DO NOT ATTEMPT!");
            }
        }
    }
}

//
//public function makeToken(string $fileName)
//{
//    if (!$this->getToken($fileName))
//        $this->setToken($fileName,bin2hex(random_bytes(32)));
//
//    return hash_hmac("sha256","Some string code for: .{$fileName}",$_SESSION['csrf_token'][$fileName]);
//}
//
//private function setToken(string $fileName,string $value)
//{
//    $_SESSION["csrf_token"][$fileName] = $value;
//}
//
//private function getToken(string $fileName)
//{
//    return $_SESSION['csrf_token'][$fileName] ?? false;
//}
//
//public function verifyToken($token)
//{
//    if ($this->get("csrf_token"))
//    {
//        if (!hash_equals($this->get("csrf_token"), $token))
//        {
//            // Proceed to process the form data
//            throw new ForbiddenException("WARNING! DO NOT ATTEMPT!");
//        }
//    }
//}