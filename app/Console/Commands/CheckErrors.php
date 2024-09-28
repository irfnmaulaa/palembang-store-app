<?php

namespace App\Console\Commands;

use App\Http\Controllers\AppErrorsController;
use Illuminate\Console\Command;

class CheckErrors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rec:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking application errors';

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

        (new AppErrorsController())->check();

        $this->info('Checking error finished');

        return 0;
    }
}
