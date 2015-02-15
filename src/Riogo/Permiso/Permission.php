<?php namespace Riogo\Permiso;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Permission
 * @package Riogo\Permiso
 */
class Permission extends Model {

    /**
     * Define the roles relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(\Config::get('permiso.role_model'), \Config::get('permiso.role_permission_table'), 'permission_id', 'role_id');
    }
}