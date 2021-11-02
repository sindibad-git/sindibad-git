<?php

namespace sindibad\zaincash\command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ZainCashMigrateCommand extends Command
{

    //Transaction Migration Path
    const MIGRATION_FILE_PATH = __DIR__ . "/../migrations/2021_10_15_195824_create_transaction_table.php";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //Command Itself
    protected $signature = 'zaincash:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    //Command Description
    protected $description = 'create transaction_zaincash table';

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
     * @return int
     */
    public function handle()
    {

        return Artisan::call("migrate",
            [
                "--path" => self::MIGRATION_FILE_PATH,
                "--realpath" => true
            ]
        );
    }
}
