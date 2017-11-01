<?php

namespace Modules\__module_name__\Api;

use App\Api\Controllers\BaseController;
use App\Models\Comment;
use Modules\__module_name__\Models\__model__;
use Request;

class __controller__ extends BaseController
{
    public function __construct()
    {
    }

    public function transform($__singular__)
    {
        $attributes = $__singular__->getAttributes();
        $attributes['images'] = $__singular__->images()->transform(function ($item) use ($__singular__) {
            return [
                'id' => $item->id,
                'title' => !empty($item->title) ?: $__singular__->title,
                'url' => get_image_url($item->url),
                'summary' => $item->summary,
            ];
        });
        isset($attributes['comments_count']) ?: $attributes['comments_count'] = $__singular__->comments_count;
        isset($attributes['favorites_count']) ?: $attributes['favorites_count'] = $__singular__->favorites_count;
        isset($attributes['follows_count']) ?: $attributes['follows_count'] = $__singular__->follows_count;
        $attributes['likes_count'] = $__singular__->likes_count;
        $attributes['clicks_count'] = $__singular__->clicks_count;
        $attributes['created_at'] = empty($__singular__->created_at) ? '' : $__singular__->created_at->toDateTimeString();
        $attributes['updated_at'] = empty($__singular__->updated_at) ? '' : $__singular__->updated_at->toDateTimeString();
        return $attributes;
    }

    /**
     * @SWG\Get(
     *   path="/__plural__",
     *   summary="获取__module_title__列表",
     *   tags={"/__plural__ __module_title__"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="string"),
     *   @SWG\Parameter(name="category_id", in="query", required=true, description="栏目ID", type="string"),
     *   @SWG\Parameter(name="tag", in="query", required=false, description="标签", type="string"),
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
        $category_id = Request::get('category_id');
        $tag = Request::get('tag');
        $page_size = Request::get('page_size') ? Request::get('page_size') : 20;
        $page = Request::get('page') ? Request::get('page') : 1;

        $key = "__singular__-list-$site_id-$category_id-$page_size-$page";

        return cache_remember($key, 1, function () use ($site_id, $tag, $page_size, $page, $category_id) {
            $__plural__ = __model__::with('items', 'tags')
                ->withCount(['comments' => function ($query) {
                    $query->where('state', Comment::STATE_PUBLISHED);
                }])
                ->withCount('favorites')
                ->withCount('follows')
                ->where('site_id', $site_id)
                ->where('category_id', $category_id)
                ->where('state', __model__::STATE_PUBLISHED)
                ->where(function ($query) use ($tag) {
                    if (!empty($tag)) {
                        $query->whereHas('tags', function ($query) use ($tag) {
                            $query->where('name', $tag);
                        });
                    }
                })
                ->orderBy('sort', 'desc')
                ->skip(($page - 1) * $page_size)
                ->limit($page_size)
                ->get();

            $__plural__->transform(function ($__singular__) {
                return $this->transform($__singular__);
            });

            return $this->responseSuccess($__plural__);
        });
    }

    /**
     * @SWG\Get(
     *   path="/__plural__/search",
     *   summary="搜索__module_title__",
     *   tags={"/__plural__ __module_title__"},
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

        $__plural__ = __model__::with('items')
            ->withCount(['comments' => function ($query) {
                $query->where('state', Comment::STATE_PUBLISHED);
            }])
            ->withCount('favorites')
            ->withCount('follows')
            ->where('site_id', $site_id)
            ->where('title', 'like', '%' . $title . '%')
            ->where('state', __model__::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $__plural__->transform(function ($__singular__) {
            return $this->transform($__singular__);
        });

        return $this->responseSuccess($__plural__);
    }

    /**
     * @SWG\Get(
     *   path="/__plural__/info",
     *   summary="获取__module_title__信息",
     *   tags={"/__plural__ __module_title__"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="__module_title__ID", type="string"),
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

        __model__::click($id);

        $key = "__plural__-info-$id";

        return cache_remember($key, 1, function () use ($id) {
            $__singular__ = __model__::find($id);
            if (empty($__singular__)) {
                return $this->responseFail('此ID不存在');
            }

            return $this->responseSuccess($this->transform($__singular__));
        });
    }

    /**
     * @SWG\Get(
     *   path="/__plural__/detail",
     *   summary="获取__module_title__详情页",
     *   tags={"/__plural__ __module_title__"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="__module_title__ID", type="string"),
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

        __model__::click($id);

        $key = "__plural__-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $__singular__ = __model__::findOrFail($id);
            $site = $__singular__->site;
            $theme = $__singular__->site->mobile_theme->name;
            $__singular__->content = replace_content_url($__singular__->content);
            return view("$theme.__module_path__.detail", compact('site', '__singular__'))->__toString();
        });
    }

    /**
     * @SWG\Get(
     *   path="/__plural__/share",
     *   summary="获取__module_title__分享页",
     *   tags={"/__plural__ __module_title__"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="__module_title__ID", type="string"),
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

        __model__::click($id);

        $key = "__plural__-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $__singular__ = __model__::findOrFail($id);
            $site = $__singular__->site;
            $theme = $__singular__->site->mobile_theme->name;
            $__singular__->content = replace_content_url($__singular__->content);
            $share = 1;
            return view("$theme.__module_path__.detail", compact('site', '__singular__', 'share'))->__toString();
        });
    }
}