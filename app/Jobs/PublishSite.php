<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Domain;
use App\Models\Module;
use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishSite implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $site;

    /**
     * Create a new job instance.
     *
     * @param Site $site
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->publish($this->site->default_theme);
        $this->publish($this->site->mobile_theme);
    }

    public function publish($theme)
    {
        try {
            $site = $this->site;
            $modules = Module::where('state', Module::STATE_ENABLE)->get();

            //创建站点目录
            $path = public_path("$site->directory/$theme->name");
            if (!is_dir($path)) {
                //创建模块目录
                @mkdir($path, 0755, true);
            }

            //生成首页
            $class = 'App\Http\\Controllers\\HomeController';
            $controller = new $class();
            $domain = new Domain($site->domain, $theme);

            $html = $controller->index($domain)->__toString();
            $html = str_replace('://localhost', '://' . $domain->site->domain, $html);
            $file_html = "$path/index.html";
            file_put_contents($file_html, $html);

            foreach ($modules as $module) {
                try {

                    $class = 'App\Http\\Controllers\\' . $module->name . 'Controller';
                    $controller = new $class();

                    $rows = call_user_func([$module->model_class, 'all']);
                    $categories = Category::where('module_id', $module->id)->get();

                    $path = public_path("$site->directory/$theme->name/$module->path");
                    if (!is_dir($path)) {
                        //创建模块目录
                        @mkdir($path, 0755, true);
                    }

                    //生成列表页
                    $html = $controller->lists($domain)->__toString();
                    $html = str_replace('://localhost', '://' . $domain->site->domain, $html);
                    $file_html = "$path/index.html";
                    file_put_contents($file_html, $html);

                    //生成栏目页
                    if ($module->fields()->where('name', 'category_id')->count()) {
                        foreach ($categories as $category) {
                            $html = $controller->category($domain, $category->id)->__toString();
                            $html = str_replace('://localhost', '://' . $domain->site->domain, $html);
                            $file_html = "$path/category-$category->id.html";
                            file_put_contents($file_html, $html);
                        }
                    }

                    //生成详情页
                    foreach ($rows as $row) {
                        $html = $controller->show($domain, $row->id)->__toString();
                        $html = str_replace('://localhost', '://' . $domain->site->domain, $html);
                        $file_html = "$path/detail-$row->id.html";
                        file_put_contents($file_html, $html);
                    }
                } catch (\Exception $exception) {
                    \Log::debug('publish ' . $module->name . ': ' . $exception->getMessage());
                }
            }
        } catch (\Exception $exception) {
            \Log::debug('publish site: ' . $exception->getMessage());
        }
    }
}
