<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use function Termwind\{render};

class RunCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Revert a new Laravel application to Laravel Mix.';

    /**
     * The current working directory.
     *
     * @var string
     */
    public string $directory = '';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->directory = getcwd();

        if (! File::exists($this->directory.'/artisan')) {
            render(<<<'HTML'
                <p class="text-red-500">This doesn't appear to be a Laravel Application. Please navigate to your application and try again.</p>
            HTML);

            return self::FAILURE;
        }

        if (File::isDirectory($this->directory.'/public/build')) {
            render(<<<'HTML'
                <div>
                    <p class="text-red-500">This doesn't appear to be a fresh Laravel Application.</p>
                    <p class="text-white mt-0">
                        See: <a class="text-sky-500" href="https://github.com/laravel/vite-plugin/blob/main/UPGRADE.md#migrating-from-vite-to-laravel-mix"> https://github.com/laravel/vite-plugin/blob/main/UPGRADE.md#migrating-from-vite-to-laravel-mix</a> to remix your app manually.
                    </p>
                </div>
            HTML);

            return self::FAILURE;
        }

        render(<<<'HTML'
            <p class="text-green-500">Remixing your app!</p>
        HTML);

        $this->publishStub('package.json', 'package.json');
        $this->publishStub('webpack.mix.js', 'webpack.mix.js');
        $this->publishStub('resources/js/bootstrap.js', 'bootstrap.js');

        $destroyVite = File::delete($this->directory.'/vite.config.js');

        if (File::exists($this->directory.'/.env.example')) {
            $this->replaceInFile('VITE_', 'MIX_', $this->directory.'/.env.example');
        }

        if (File::exists($this->directory.'/.env')) {
            $this->replaceInFile('VITE_', 'MIX_', $this->directory.'/.env');
        }

        if (! $destroyVite) {
            render(<<<'HTML'
                <p class="text-yellow-500 mt-0">Failed to delete vite.config.js</p>
            HTML);
        }

        render(<<<'HTML'
            <p class="text-green-500 mt-0">Successfully 'Remixed' your app!</p>
        HTML);
    }

    /**
     * Publish a file to the app.
     *
     * @param  string  $file
     * @param  string  $stub
     * @return void
     */
    protected function publishStub(string $file, string $stub): void
    {
        File::put($this->directory.'/'.ltrim($file, '/'), File::get(base_path('stubs/'.ltrim($stub, '/'))));
    }

    /**
     * Replace the given string in the given file.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $file
     * @return void
     */
    protected function replaceInFile(string $search, string $replace, string $file)
    {
        file_put_contents(
            $file,
            str_replace($search, $replace, file_get_contents($file))
        );
    }
}
