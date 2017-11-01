<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Module;
use App\Models\Site;
use Illuminate\Console\Command;

class BuildHtmlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:html {site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the html file for the site.';

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
        $site = Site::find($this->argument('site'));

        $site->publish($site->default_theme);
        $site->publish($site->mobile_theme, 'iPhone');

        $this->info($site->title . '静态页面生成完成!');
    }
}
