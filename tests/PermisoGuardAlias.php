<?php

use Riogo\Permiso\PermisoGuard;

class PermisoGuardAlias extends PermisoGuard
{
    public function user()
    {
        return User::find(1);
    }

    public function get($varName)
    {
        return $this->{$varName};
    }

    public function clearUserDataFromStorage()
    {
        parent::clearUserDataFromStorage();
    }
}