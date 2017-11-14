<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-2
 * Time: 上午10:45
 */

namespace App\Models;

class AuthenticatedUser
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * @return \Zdp\ServiceProvider\Data\Models\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
