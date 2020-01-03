<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Console;

use Illuminate\Console\Command;
use Mallto\Admin\Seeder\TablesSeeder;

class UpdateCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'admin_enhance:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the admin package';

    /**
     * Install directory.
     *
     * @var string
     */
    protected $directory = '';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('db:seed', [ '--class' => TablesSeeder::class, '--force' => true ]);
    }

}
