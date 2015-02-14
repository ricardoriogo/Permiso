<?php namespace Riogo\Permiso;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * Model Role
 * @package Riogo\Permiso
 */
class Role extends Model {

    /**
     * Define the users relationship, this uses config auth.model to define the auth model class name.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Config::get('auth.model'), 'assigned_roles', 'role_id', 'user_id');
    }

    /**
     * Define the permissions relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany('Riogo\\Permiso\\Permission', 'roles_permissions', 'role_id', 'permission_id');
    }
}