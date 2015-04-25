# Permiso 
--------------------------------------

[![Build Status](https://travis-ci.org/ricardoriogo/Permiso.svg?branch=master)](https://travis-ci.org/ricardoriogo/Permiso)

A simple Laravel 5 package for Role-based permissions.

## Instalation
### 1. Add to composer.json
In the `require` key of `composer.json` file add the following:

    "ricardoriogo/permiso": "dev-master"

Run composer update command.
    
    $ composer update

### 2. Register Service Provider
In `config/app.php` add `Riogo\Permiso\PermisoServiceProvider` to the end of `$providers` array.

```php
    'providers' => array(
		'App\Providers\EventServiceProvider',
		'App\Providers\RouteServiceProvider',
        ...
        'Riogo\Permiso\PermisoServiceProvider',
    ),
```

### 3. Change Auth configuration
In `config/auth.php` change the `driver` configuration to `permiso`.
Permiso will use `App\User` model by default. You will need to change `model` configuration on `config/auth.php` if you're using another model for authentication.

### 4. Add UserRoleTrait to your auth model
In your auth model add the __UserRoleTrait__ trait. By default __App\User__ is the model used for authentication.
```php    
    class User extends Model implements AuthenticatableContract, CanResetPasswordContract {
        
        use Riogo\Permiso\UserRolesTrait
        ...
    }    
```

### 5. Run Migrations
To create the migration file for roles and permissions tables use the command:

    $ php artisan permiso:migration

This will create a migration file on `database/migrations`.
Then use migrate command.

    $ php artisan migrate

----------------------------

## How to use
Permiso extends Auth class capabilities for checking if authenticated user have especific roles or permissions.

### Checking for a Role
To check for a role you have to use `hasRole()` method.
```php    
    if (Auth::hasRole('admin')) {
        // Actions for this Role
    }
```

You can use the alias method `is()` too.
```php    
     if (Auth::is('admin')) {
        // Actions for this Role
     }
```

### Checking for multiple Roles
It's possible check for multiple roles, passing an array with the roles or a string with comma separated values.

```php
    // Using an array
    if (Auth::hasRole(['admin', 'member'])) {
        // Actions for this Roles
    }
    
    // Same result with string
    if (Auth::hasRole('admin, member')) {
        // Actions for this Roles
    }
```

It will return true if user have one or more of this roles.

If you want to check if user have all roles use the method `checkAll()` before `hasRole()`.

```php
    // Will return true if user have admin and member roles.
    if (Auth::checkAll()->hasRole(['admin', 'member'])) {
        // Actions for this Roles
    }
```

### Checking for a Permission
All uses of role are applicable in permissions using `hasPermission()` ou your alias `can()`.

```php
    if (Auth::hasPermission('users.list')) {
        // Actions for this Permission
    }
    
    if (Auth::checkAll()->can('users.delete, users.create')) {
        // Actions for this Permissions
    }
```

### Variant of `is()` method
For check one role it's possible to use a variant of `is()`, it use a magic method to define a role to check. See the examples:
 * `Auth::isAdmin()` will return true if user have __admin__ role.
 * `Auth::isMember()` will return true if user have __member__ role.
 * `Auth::isRoleWithLongName()` will return true if user have __role_with_long_name__ role.

---------------------------------

## Configuration
If you will use your own models for Role and Permission or change the default tables names, publish the configuration file using
    
    $ php artisan vendor:publish --provider="Riogo\Permiso\PermisoServiceProvider"
    
And change all configuration needed in `config/permiso.php`.