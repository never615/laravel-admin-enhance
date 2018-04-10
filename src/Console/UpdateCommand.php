<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Console;


use Illuminate\Console\Command;
use Mallto\Admin\Seeder\PemissionSeeder;

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
    public function fire()
    {
//        $this->call('migrate', ['--path' => str_replace(base_path(), '', __DIR__) . '/../../migrations/']);
        $this->call('db:seed', ['--class' => PemissionSeeder::class]);
    }

}
