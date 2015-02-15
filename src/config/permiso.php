<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Role Model
    |--------------------------------------------------------------------------
    |
    | This is the Role model used by Permiso. If you want to create your own,
    | change this value.
    |
    */
    'role_model' => '\Riogo\Permiso\Role',

    /*
    |--------------------------------------------------------------------------
    | Roles Table
    |--------------------------------------------------------------------------
    |
    | This is the Roles table used by Permiso.
    |
    */
    'roles_table' => 'roles',

    /*
    |--------------------------------------------------------------------------
    | Permission Model
    |--------------------------------------------------------------------------
    |
    | This is the Permission model used by Permiso. If you want to create your
    | own, change this value.
    |
    */
    'permission_model' => '\Riogo\Permiso\Permission',

    /*
    |--------------------------------------------------------------------------
    | Permissions Table
    |--------------------------------------------------------------------------
    |
    | This is the Permissions table used by Permiso.
    |
    */
    'permissions_table' => 'permissions',

    /*
    |--------------------------------------------------------------------------
    | Permission_role Table
    |--------------------------------------------------------------------------
    |
    | This table will save relationship between roles and permissions to the database.
    |
    */
    'role_permission_table' => 'role_permission',

    /*
    |--------------------------------------------------------------------------
    | User_role Table
    |--------------------------------------------------------------------------
    |
    | This table will save relationship between users and roles to the database.
    |
    */
    'user_role_table' => 'user_role',

);