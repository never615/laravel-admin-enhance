<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Console;


use Illuminate\Console\Command;
use Mallto\Admin\Seeder\MenuSeeder;
use Mallto\Admin\Seeder\PemissionSeeder;
use Malto\Admin\Seeder\AdminTablesSeeder;

class SeederCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'admin_enhance:seed {--T|--type=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the admin seeder.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $type = $this->option("type");

        switch ($type) {
            case 'default':
                $this->call('db:seed', ['--class' => AdminTablesSeeder::class]);
                break;
            case 'menu':
                $this->call('db:seed', ['--class' => MenuSeeder::class]);
                break;
            case 'permission':
                $this->call('db:seed', ['--class' => PemissionSeeder::class]);
                break;
            default:
                $this->error('type 值输入错误:可选[default,menu,permission]!');
                break;
        }

    }
}
