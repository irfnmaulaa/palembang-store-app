<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserActiveStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-active-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user is_active status to false daily';

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
        User::where('role', 'staff')->update([
            'is_active' => false
        ]);
        $this->info('User active status updated successfully.');
        return 0;
    }
}
