<?php
/**
 * Class Auth
 * @package app\core
 * @author Darwin Marcelo <akosiyawin@gmail.com>
 */

namespace app\core;


use app\core\database\UserModel;

class Auth
{

    private Session $session;
    public ?UserModel $userModel = null;


    public function __construct(Session $session,string $userClass)
    {
        $this->session = $session;
        if($this->session->get("_user"))
        {
            /** @var $userClass UserModel*/
//            $primaryKey = $userClass::primaryKey();
            $this->userModel = $userClass::findOne($this->session->get("_user"));
        }
        else
        {
            $this->userModel = null;
        }
    }

    public function isGuest()
    {
        //The same with $this->userModel ? false : $this->userModel;
        return !$this->userModel;
    }

    //set the authentication value of user, means we are logged-in
    public function set(UserModel $userModel)
    {
        $this->userModel = $userModel;
        $primaryKey = $userModel->primaryKey();
        $primaryValue = $userModel->{$primaryKey};
        $this->session->set("_user",$primaryValue);
        return true;
    }

    public function unset()
    {
        $this->userModel = null;
        $this->session->remove("_user");
        return true;
    }

}