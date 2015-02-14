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

    public function testHasRole()
    {
        $permiso = $this->getProvider();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($permiso->hasRole('admin'));
        $this->assertTrue($permiso->hasRole('member'));
        $this->assertFalse($permiso->hasRole('another_role'));
        $this->assertTrue($permiso->is('member'));
        $this->assertFalse($permiso->is('another_role'));
    }

    public function testIsRole()
    {
        $permiso = $this->getProvider();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($permiso->isAdmin());
        $this->assertFalse($permiso->isAnotherRole());
        $this->assertFalse($permiso->isAnotherRoleExtensiveName());
        $this->assertTrue($permiso->isAnotherRoleExtensiveNameOk());
        $this->assertFalse($permiso->callAnotherThing());
    }

    public function testHasMoreThanOneRole()
    {
        $permiso = $this->getProvider();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($permiso->is(['anotherfalse', 'admin']));
        $this->assertTrue($permiso->is('anotherfalse, admin'));
        $this->assertFalse($permiso->checkAll()->is(['anotherfalse', 'admin']));
        $this->assertTrue($permiso->checkAll()->is(['member', 'admin']));
        $this->assertTrue($permiso->checkAll()->is('member,admin'));
        $this->assertTrue($permiso->is(['anotherfalse', 'admin']));
    }

    public function testHasPermission()
    {
        $permiso = $this->getProvider();

        // Assertion
        $this->assertTrue($permiso->can('user.delete'));
        $this->assertFalse($permiso->can('user.destroy'));
    }

    public function testHasMoreThanOnePermission()
    {
        $permiso = $this->getProvider();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertFalse($permiso->can('user.destroy, user.view'));
        $this->assertTrue($permiso->can('user.destroy, user.delete'));
        $this->assertFalse($permiso->checkAll()->can('user.destroy, user.delete'));
        $this->assertTrue($permiso->checkAll()->can('user.delete, post.view'));
    }

    public function testClearUserDataFromStorage()
    {
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
        $permiso->hasRole('admin');

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
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
        $session->shouldReceive('get')->with('roles')->andReturn(['admin', 'member', 'another_role_extensive_name_ok']);
        $session->shouldReceive('get')->with('permissions')->andReturn(['user.delete', 'post.view']);

        $permiso = new PermisoGuardAlias($provider, $session);

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'laracl',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertTrue($permiso->isAdmin());
        $this->assertFalse($permiso->checkAll()->can('user.delete, post.view'));
        $this->assertTrue($permiso->checkAll()->can('admin.users.delete, admin.users.list'));
    }
}