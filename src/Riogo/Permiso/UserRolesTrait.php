<?php namespace Riogo\Permiso;

/**
 * This trait must be included in auth model.
 *
 * @package Riogo\Permiso
 */
trait UserRolesTrait {

	/**
	 * Define the relationship with the auth model.
	 *
	 * @return mixed
	 */
	public function roles()
	{
		return $this->belongsToMany('Riogo\Permiso\Role', 'assigned_roles', 'user_id', 'role_id');
	}
}