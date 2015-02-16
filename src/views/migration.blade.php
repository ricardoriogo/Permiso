<?php echo '<?php' ?>

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PermisoTables extends Migration {

    /**
    * Run the migrations.
    *
    * @return  void
    */
    public function up()
    {
        Schema::create(\Config::get('permiso.roles_table'), function(Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create(\Config::get('permiso.permissions_table'), function(Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create(\Config::get('permiso.user_role_table'), function(Blueprint $table)
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

            $table->foreign('role_id')->references('id')->on(\Config::get('permiso.roles_table'));
        });

        Schema::create(\Config::get('permiso.role_permission_table'), function(Blueprint $table)
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
    }

    /**
    * Reverse the migrations.
    *
    * @return  void
    */
    public function down()
    {
        Schema::table(\Config::get('permiso.user_role_table'), function (Blueprint $table) {
            $table->dropForeign(\Config::get('permiso.user_role_table') . '_user_id_foreign');
            $table->dropForeign(\Config::get('permiso.user_role_table') . '_role_id_foreign');
        });

        Schema::table(\Config::get('permiso.role_permission_table'), function (Blueprint $table) {
            $table->dropForeign(\Config::get('permiso.role_permission_table') . '_role_id_foreign');
            $table->dropForeign(\Config::get('permiso.role_permission_table') . '_permission_id_foreign');
        });

        Schema::drop(\Config::get('permiso.user_role_table'));
        Schema::drop(\Config::get('permiso.role_permission_table'));
        Schema::drop(\Config::get('permiso.roles_table'));
        Schema::drop(\Config::get('permiso.permissions_table'));
    }
}
