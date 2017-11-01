<?php

namespace Modules\Page\Api;

use App\Api\Controllers\BaseController;
use Modules\Page\Models\Page;
use Request;

class PageController extends BaseController
{
    public function __construct()
    {
    }

    public function transform($page)
    {
        $attributes = $page->getAttributes();
        $attributes['images'] = $page->images()->transform(function ($item) use ($page) {
            return [
                'id' => $item->id,
                'title' => !empty($item->title) ?: $page->title,
                'url' => get_image_url($item->url),
                'summary' => $item->summary,
            ];
        });
        $attributes['comment_count'] = $page->comment_count;
        $attributes['favorite_count'] = $page->favorite_count;
        $attributes['follow_count'] = $page->follow_count;
        $attributes['like_count'] = $page->like_count;
        $attributes['click_count'] = $page->click_count;
        $attributes['created_at'] = empty($page->created_at) ? '' : $page->created_at->toDateTimeString();
        $attributes['updated_at'] = empty($page->updated_at) ? '' : $page->updated_at->toDateTimeString();
        return $attributes;
    }

    /**
     * @SWG\Get(
     *   path="/pages",
     *   summary="获取页面列表",
     *   tags={"/pages 页面"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="string"),
     *   @SWG\Parameter(name="page_size", in="query", required=true, description="分页大小", type="integer"),
     *   @SWG\Parameter(name="page", in="query", required=true, description="分页序号", type="integer"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function lists()
    {
        $site_id = Request::get('site_id') ? Request::get('site_id') : 1;
        $page_size = Request::get('page_size') ? Request::get('page_size') : 20;
        $page = Request::get('page') ? Request::get('page') : 1;

        $key = "page-list-$site_id-$page_size-$page";

        return cache_remember($key, 1, function () use ($site_id, $page_size, $page) {
            $pages = Page::with('items')
                ->where('site_id', $site_id)
                ->where('state', Page::STATE_PUBLISHED)
                ->orderBy('sort', 'desc')
                ->skip(($page - 1) * $page_size)
                ->limit($page_size)
                ->get();

            $pages->transform(function ($page) {
                return $this->transform($page);
            });

            return $this->responseSuccess($pages);
        });
    }

    /**
     * @SWG\Get(
     *   path="/pages/search",
     *   summary="搜索页面",
     *   tags={"/pages 页面"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="string"),
     *   @SWG\Parameter(name="title", in="query", required=true, description="搜索标题", type="string"),
     *   @SWG\Parameter(name="page_size", in="query", required=true, description="分页大小", type="integer"),
     *   @SWG\Parameter(name="page", in="query", required=true, description="分页序号", type="integer"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function search()
    {
        $site_id = Request::get('site_id') ? Request::get('site_id') : 1;
        $page_size = Request::get('page_size') ? Request::get('page_size') : 20;
        $page = Request::get('page') ? Request::get('page') : 1;
        $title = Request::get('title');

        $pages = Page::with('items')
            ->where('site_id', $site_id)
            ->where('title', 'like', '%' . $title . '%')
            ->where('state', Page::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $pages->transform(function ($page) {
            return $this->transform($page);
        });

        return $this->responseSuccess($pages);
    }

    /**
     * @SWG\Get(
     *   path="/pages/info",
     *   summary="获取页面信息",
     *   tags={"/pages 页面"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="页面ID", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function info()
    {
        $id = Request::get('id');

        Page::click($id);

        $key = "pages-info-$id";

        return cache_remember($key, 1, function () use ($id) {
            $page = Page::find($id);
            if (empty($page)) {
                return $this->responseFail('此ID不存在');
            }

            return $this->responseSuccess($this->transform($page));
        });
    }

    /**
     * @SWG\Get(
     *   path="/pages/detail",
     *   summary="获取页面详情页",
     *   tags={"/pages 页面"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="页面ID", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function detail()
    {
        $id = Request::get('id');

        Page::click($id);

        $key = "pages-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $page = Page::where('id', $id)
                ->where('state', Page::STATE_PUBLISHED)
                ->first();
            $site = $page->site;
            $theme = $page->site->mobile_theme->name;
            $page->content = replace_content_url($page->content);
            return view("$theme.page.detail", compact('site', 'page'))->__toString();
        });
    }

    /**
     * @SWG\Get(
     *   path="/pages/share",
     *   summary="获取页面分享页",
     *   tags={"/pages 页面"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="页面ID", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function share()
    {
        $id = Request::get('id');

        Page::click($id);

        $key = "pages-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $page = Page::where('id', $id)
                ->where('state', Page::STATE_PUBLISHED)
                ->first();
            $site = $page->site;
            $theme = $page->site->mobile_theme->name;
            $page->content = replace_content_url($page->content);
            $share = 1;
            return view("$theme.page.detail", compact('site', 'page', 'share'))->__toString();
        });
    }
}