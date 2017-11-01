<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Swagger;

class SwaggerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make swagger json.';

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
    public function handle()
    {
        $paths = [base_path('app/Api'), base_path('modules')];

        $options = array(
            'output' => base_path('public/api-docs/swagger.json'),
            'stdout' => false,
            'exclude' => null,
            'bootstrap' => false,
            'help' => false,
            'version' => false,
            'debug' => false,
        );

        $version = '2.0.11';
        error_log('');
        error_log('Swagger-PHP ' . $version);
        error_log('------------' . str_repeat('-', strlen($version)));

        $swagger = Swagger\scan($paths, ['exclude' => null]);
        $methods = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch'];
        $counter = 0;

        foreach ($swagger->paths as $path) {
            foreach ($path as $method => $operation) {
                if ($operation !== null && in_array($method, $methods)) {
                    error_log(str_pad($method, 7, ' ', STR_PAD_LEFT) . ' ' . $path->path);
                    $counter++;
                }
            }
        }
        error_log('----------------------' . str_repeat('-', strlen($counter)));
        error_log($counter . ' operations documented');
        error_log('----------------------' . str_repeat('-', strlen($counter)));
        if ($options['stdout']) {
            echo $swagger;
        } else {
            if (is_dir($options['output'])) {
                $options['output'] .= '/swagger.json';
            }
            $swagger->saveAs($options['output']);
            error_log('Written to ' . realpath($options['output']));
        }
        error_log('');
    }
}
