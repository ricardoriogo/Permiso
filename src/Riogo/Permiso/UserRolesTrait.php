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
		return $this->belongsToMany(\Config::get('permiso.role_model'), \Config::get('permiso.user_role_table'), 'user_id', 'role_id');
	}
}