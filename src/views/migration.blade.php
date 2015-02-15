<?php echo '<?php' ?>

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration {

    /**
    * Run the migrations.
    *
    * @return  void
    */
    public function up()
    {
        Schema::create('roles', function(Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function(Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('assigned_roles', function(Blueprint $table)
        {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();

            $table->primary(array('user_id', 'role_id'));

            $userModel   = \Config::get('auth.model');
            $userKeyName = (new $userModel())->getKeyName();
            $table->foreign('user_id')->references($userKeyName)
                ->on(\Config::get('auth.table'))
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('role_id')->references('id')->on('roles');
        });

        Schema::create('roles_permissions', function(Blueprint $table)
        {
            $table->integer('role_id')->unsigned();
            $table->integer('permission_id')->unsigned();

            $table->primary(array('role_id', 'permission_id'));

            $table->foreign('role_id')->references('id')
                ->on('roles')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('permission_id')->references('id')
                ->on('permissions')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return  void
    */
    public function down()
    {
        Schema::table('assigned_roles', function (Blueprint $table) {
            $table->dropForeign('assigned_roles_user_id_foreign');
            $table->dropForeign('assigned_roles_role_id_foreign');
        });

        Schema::table('roles_permissions', function (Blueprint $table) {
            $table->dropForeign('roles_permissions_role_id_foreign');
            $table->dropForeign('roles_permissions_permission_id_foreign');
        });

        Schema::drop('assigned_roles');
        Schema::drop('roles_permissions');
        Schema::drop('roles');
        Schema::drop('permissions');
    }
}
