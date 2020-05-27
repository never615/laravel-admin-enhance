<?php

namespace Mallto\Admin\Console;

use Illuminate\Console\Command;
use Mallto\Admin\Seeder\InitDataSeeder;
use Mallto\Admin\Seeder\TablesSeeder;

class InstallCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'admin_enhance:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the admin_enhance package';

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
        $this->initDatabase();

        $this->initAdminDirectory();
    }


    /**
     * Create tables and seed it.
     *
     * @return void
     */
    public function initDatabase()
    {
        $this->call('migrate', [ '--path' => str_replace(base_path(), '', __DIR__) . '/../../migrations/' ]);
        $this->call('db:seed', [ '--class' => InitDataSeeder::class ]);
        $this->call('db:seed', [ '--class' => TablesSeeder::class ]);
    }


    /**
     * Initialize the admAin directory.
     *
     * @return void
     */
    protected function initAdminDirectory()
    {
        //检查Admin目录是否存在
        $this->directory = config('admin.directory');
        if (is_dir($this->directory)) {
            //已经存在
        } else {
            //不存在
            //创建Admin目录
            $this->makeDir('/');
            $this->line('<info>Admin directory was created:</info> ' . str_replace(base_path(), '',
                    $this->directory));
        }

        //创建或者替换bootstrap文件和route文件
        $this->createBootstrapFile();
        $this->createRoutesFile();
    }


    /**
     * Create routes file.
     *
     * @return void
     */
    protected function createBootstrapFile()
    {
        $file = $this->directory . '/bootstrap.php';

        $contents = $this->getStub('bootstrap');
//        $this->laravel['files']->put($file, $contents);
        $this->laravel['files']->append($file, $contents);
        $this->line('<info>Bootstrap file was created:</info> ' . str_replace(base_path(), '', $file));
    }


    /**
     * Create routes file.
     *
     * @return void
     */
    protected function createRoutesFile()
    {
        $file = $this->directory . '/routes.php';

        $contents = $this->getStub('routes');
        $this->laravel['files']->put($file,
            str_replace('DummyNamespace', config('admin.route.namespace'), $contents));
        $this->line('<info>Routes file was created:</info> ' . str_replace(base_path(), '', $file));
    }


    /**
     * Get stub contents.
     *
     * @param $name
     *
     * @return string
     */
    protected function getStub($name)
    {
        return $this->laravel['files']->get(__DIR__ . "/stubs/$name.stub");
    }


    /**
     * Make new directory.
     *
     * @param string $path
     */
    protected function makeDir($path = '')
    {
        $this->laravel['files']->makeDirectory("{$this->directory}/$path", 0755, true, true);
    }
}
