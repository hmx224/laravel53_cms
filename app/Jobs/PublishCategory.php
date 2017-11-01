<?php

namespace App\Jobs;

use App\Http\Controllers\WebController;
use App\Models\Category;
use App\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishCategory implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $category;

    /**
     * Create a new job instance.
     *
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->publish($this->category->site->default_theme);
        $this->publish($this->category->site->mobile_theme);
    }

    public function publish($theme)
    {
        try {
            $category = $this->category;
            $site = $category->site;
            $module = $category->module;
            $domain = new Domain($site->domain, $theme);

            //创建栏目目录
            $path = public_path("$site->directory/$theme->name/$module->path/$category->full_path");
            if (!is_dir($path)) {
                //创建模块目录
                @mkdir($path, 0755, true);
            }

            try {
                //生成列表页（分页）
                $controller = new WebController();
                $html = $controller->list($domain, $module->path, $category->full_path, 'index', 1)->__toString();
                $html = str_replace('://localhost', '://' . $domain->site->domain, $html);
                $file_html = "$path/index.html";
                file_put_contents($file_html, $html);

                //生成详情页（日期）

                //生成附加页
            } catch (\Exception $exception) {
                \Log::debug('publish ' . $category->name . ': ' . $exception->getMessage());
            }
        } catch (\Exception $exception) {
            \Log::debug('publish category: ' . $exception->getMessage());
        }
    }
}
