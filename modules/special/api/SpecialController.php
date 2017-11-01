<?php

namespace Modules\Special\Api;

use App\Api\Controllers\BaseController;
use Modules\Special\Models\Special;
use Request;

class SpecialController extends BaseController
{
    public function __construct()
    {
    }

    public function transform($special)
    {
        $attributes = $special->getAttributes();
        $attributes['images'] = $special->images()->transform(function ($item) use ($special) {
            return [
                'id' => $item->id,
                'title' => !empty($item->title) ?: $special->title,
                'url' => get_image_url($item->url),
                'summary' => $item->summary,
            ];
        });
        $attributes['comment_count'] = $special->comment_count;
        $attributes['favorite_count'] = $special->favorite_count;
        $attributes['follow_count'] = $special->follow_count;
        $attributes['like_count'] = $special->like_count;
        $attributes['click_count'] = $special->click_count;
        $attributes['created_at'] = empty($special->created_at) ? '' : $special->created_at->toDateTimeString();
        $attributes['updated_at'] = empty($special->updated_at) ? '' : $special->updated_at->toDateTimeString();
        return $attributes;
    }

    /**
     * @SWG\Get(
     *   path="/specials",
     *   summary="获取专题列表",
     *   tags={"/specials 专题"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="string"),
     *   @SWG\Parameter(name="category_id", in="query", required=true, description="栏目ID", type="string"),
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
        $page_size = Request::get('page_size') ? Request::get('page_size') : 20;
        $page = Request::get('page') ? Request::get('page') : 1;

        $key = "special-list-$site_id-$category_id-$page_size-$page";

        return cache_remember($key, 1, function () use ($site_id, $page_size, $page, $category_id) {
            $specials = Special::with('items')
                ->where('site_id', $site_id)
                ->where('category_id', $category_id)
                ->where('state', Special::STATE_PUBLISHED)
                ->orderBy('sort', 'desc')
                ->skip(($page - 1) * $page_size)
                ->limit($page_size)
                ->get();

            $specials->transform(function ($special) {
                return $this->transform($special);
            });

            return $this->responseSuccess($specials);
        });
    }

    /**
     * @SWG\Get(
     *   path="/specials/search",
     *   summary="搜索专题",
     *   tags={"/specials 专题"},
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

        $specials = Special::with('items')
            ->where('site_id', $site_id)
            ->where('title', 'like', '%' . $title . '%')
            ->where('state', Special::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $specials->transform(function ($special) {
            return $this->transform($special);
        });

        return $this->responseSuccess($specials);
    }

    /**
     * @SWG\Get(
     *   path="/specials/info",
     *   summary="获取专题信息",
     *   tags={"/specials 专题"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="专题ID", type="string"),
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

        Special::click($id);

        $key = "specials-info-$id";

        return cache_remember($key, 1, function () use ($id) {
            $special = Special::find($id);
            if (empty($special)) {
                return $this->responseFail('此ID不存在');
            }

            return $this->responseSuccess($this->transform($special));
        });
    }

    /**
     * @SWG\Get(
     *   path="/specials/detail",
     *   summary="获取专题详情页",
     *   tags={"/specials 专题"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="专题ID", type="string"),
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

        Special::click($id);

        $key = "specials-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $special = Special::findOrFail($id);
            $site = $special->site;
            $theme = $special->site->mobile_theme->name;
            $special->content = replace_content_url($special->content);
            return view("$theme.specials.detail", compact('site', 'special'))->__toString();
        });
    }

    /**
     * @SWG\Get(
     *   path="/specials/share",
     *   summary="获取专题分享页",
     *   tags={"/specials 专题"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="专题ID", type="string"),
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

        Special::click($id);

        $key = "specials-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $special = Special::findOrFail($id);
            $site = $special->site;
            $theme = $special->site->mobile_theme->name;
            $special->content = replace_content_url($special->content);
            $share = 1;
            return view("$theme.specials.detail", compact('site', 'special', 'share'))->__toString();
        });
    }
}