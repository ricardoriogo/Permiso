<?php namespace Riogo\Permiso\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrationCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'permiso:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create migrations for use with Permiso';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('This command will create the migrations requested for Permiso.');

        $this->laravel->view->addNamespace('permiso', substr(__DIR__, 0, -22).'views');

        if ($this->confirm("Proceed with the migration creation? [Yes|no]")) {
            $this->line('');
            $this->info("Creating migration...");
            if ($this->createMigration()) {
                $this->info("Migration successfully created!");
            } else {
                $this->error(
                    "Coudn't create migration.\n Check the write permissions".
                    " within the app/database/migrations directory."
                );
            }
            $this->line('');
        }
    }

    /**
     * Create the migration.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function createMigration()
    {
        $migrationFile = base_path("/database/migrations")."/".date('Y_m_d_His')."_permiso_tables.php";
        $output = $this->laravel->view->make('permiso::migration')->render();
        if (!file_exists($migrationFile) && $fs = fopen($migrationFile, 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }
        return false;
    }
}
