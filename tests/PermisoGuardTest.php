<?php

use Riogo\Permiso\PermisoGuard;
use Illuminate\Support\Facades\Facade;
use Mockery as m;
use Illuminate\Database\Capsule\Manager as Capsule;


class PermisoGuardTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * Initialize PermisoGuard
     *
     * @return PermisoGuard
     */
    public function getProvider()
    {
        $model = 'User';
        $hasher = m::mock('\Illuminate\Contracts\Hashing\Hasher');
        $provider = new \Illuminate\Auth\EloquentUserProvider($hasher, $model);

        $session = m::mock('\Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->shouldReceive('has')->andReturn(true);
        $session->shouldReceive('get')->with('roles')->andReturn(['admin', 'member', 'another_role_extensive_name_ok']);
        $session->shouldReceive('get')->with('permissions')->andReturn(['user.delete', 'post.view']);

        return new PermisoGuard($provider, $session);
    }

    /**
     * Setup database connection for tests
     */
    public function setUp()
    {
        /*
         * Setup connection with sqlite
         */
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => ''
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        /*
         * Create the tables user, roles, permissions and their relationships
         */
        $capsule->schema()->create('users', function($table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->rememberToken();
            $table->timestamps();
        });

        $capsule->schema()->create(\Config::get('permiso.roles_table'), function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $capsule->schema()->create(\Config::get('permiso.permissions_table'), function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $capsule->schema()->create(\Config::get('permiso.user_role_table'), function($table)
        {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->primary(array('user_id', 'role_id'));

            $table->foreign('user_id')->references('user_id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('role_id')->references('id')->on(\Config::get('permiso.roles_table'));
        });

        $capsule->schema()->create(\Config::get('permiso.role_permission_table'), function($table)
        {
            $table->integer('role_id')->unsigned();
            $table->integer('permission_id')->unsigned();

            $table->primary(array('role_id', 'permission_id'));

            $table->foreign('role_id')->references('id')
                ->on(\Config::get('permiso.roles_table'))
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('permission_id')->references('id')
                ->on(\Config::get('permiso.permissions_table'))
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });

        /*
         * Seed the database
         */
        $user = new User();
        $user->name = "Admin";
        $user->email = 'teste@mail.com';
        $user->password = "Admin";
        $user->save();

        $role = new \Riogo\Permiso\Role();
        $role->name = 'admin';
        $role = $user->roles()->save($role);

        $permission = new \Riogo\Permiso\Permission();
        $permission->name = 'user.delete';
        $role->permissions()->save($permission);

        $permission = new \Riogo\Permiso\Permission();
        $permission->name = 'user.list';
        $role->permissions()->save($permission);
    }

    public function testHasRole()
    {
        // Get PermisoGuard instance
        $permiso = $this->getProvider();

        // Assertion
        $this->assertTrue($permiso->hasRole('admin'));
        $this->assertTrue($permiso->hasRole('member'));
        $this->assertFalse($permiso->hasRole('another_role'));
        $this->assertTrue($permiso->is('member'));
        $this->assertFalse($permiso->is('another_role'));
    }

    public function testIsRole()
    {
        // Get PermisoGuard instance
        $permiso = $this->getProvider();

        // Assertion
        $this->assertTrue($permiso->isAdmin());
        $this->assertFalse($permiso->isAnotherRole());
        $this->assertFalse($permiso->isAnotherRoleExtensiveName());
        $this->assertTrue($permiso->isAnotherRoleExtensiveNameOk());
        $this->assertFalse($permiso->callAnotherThing());
    }

    public function testHasMoreThanOneRole()
    {
        // Get PermisoGuard instance
        $permiso = $this->getProvider();

        // Assertion
        $this->assertTrue($permiso->is(['anotherfalse', 'admin']));
        $this->assertTrue($permiso->is('anotherfalse, admin'));
        $this->assertFalse($permiso->checkAll()->is(['anotherfalse', 'admin']));
        $this->assertTrue($permiso->checkAll()->is(['member', 'admin']));
        $this->assertTrue($permiso->checkAll()->is('member,admin'));
        $this->assertTrue($permiso->is(['anotherfalse', 'admin']));
    }

    public function testHasPermission()
    {
        // Get PermisoGuard instance
        $permiso = $this->getProvider();

        // Assertion
        $this->assertTrue($permiso->can('user.delete'));
        $this->assertFalse($permiso->can('user.destroy'));
    }

    public function testHasMoreThanOnePermission()
    {
        // Get PermisoGuard instance
        $permiso = $this->getProvider();

        // Assertion
        $this->assertFalse($permiso->can('user.destroy, user.view'));
        $this->assertTrue($permiso->can('user.destroy, user.delete'));
        $this->assertFalse($permiso->checkAll()->can('user.destroy, user.delete'));
        $this->assertTrue($permiso->checkAll()->can('user.delete, post.view'));
    }

    public function testClearUserDataFromStorage()
    {
        // Configuration
        $model = 'User';
        $hasher = m::mock('\Illuminate\Contracts\Hashing\Hasher');
        $provider = new \Illuminate\Auth\EloquentUserProvider($hasher, $model);

        $session = m::mock('\Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->shouldReceive('has')->andReturn(true);
        $session->shouldReceive('get')->with('roles')->andReturn(['admin', 'member', 'another_role_extensive_name_ok']);
        $session->shouldReceive('get')->with('permissions')->andReturn(['user.delete', 'post.view']);
        $session->shouldReceive('remove')->with(\Mockery::any())->andReturn(true);
        $session->shouldReceive('remove')->with('roles')->andReturn(true);
        $session->shouldReceive('remove')->with('permissions')->andReturn(true);

        $permiso = new PermisoGuardAlias($provider, $session);

        $cookie = m::mock('\Illuminate\Contracts\Cookie\QueueingFactory');
        $cookie->shouldReceive('forget', 'queue')->andReturn(true);

        $permiso->setCookieJar($cookie);

        // Used only for populate $permissions and $roles properties
        $permiso->hasRole('admin');

        // Assertion
        $this->assertEmpty($permiso->clearUserDataFromStorage());
        $this->assertSame([], $permiso->get('roles'));
        $this->assertSame([], $permiso->get('permissions'));
    }

    public function testLoadRolesAndPermissionsFromDatabase()
    {
        $model = 'User';
        $hasher = m::mock('\Illuminate\Contracts\Hashing\Hasher');
        $provider = new \Illuminate\Auth\EloquentUserProvider($hasher, $model);

        $session = m::mock('\Symfony\Component\HttpFoundation\Session\SessionInterface');
        $session->shouldReceive('has')->andReturn(false);
        $session->shouldReceive('put')->with(m::any())->andReturn(true);

        $permiso = new PermisoGuardAlias($provider, $session);

        // Assertion
        $this->assertTrue($permiso->isAdmin());
        $this->assertFalse($permiso->checkAll()->can('user.delete, post.view'));
        $this->assertTrue($permiso->checkAll()->can('user.delete, user.list'));
        $this->assertSame(['admin'], $permiso->get('roles'));
        $this->assertSame(['user.delete', 'user.list'], $permiso->get('permissions'));
    }
}