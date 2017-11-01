<?php

namespace Modules\Video\Api;

use App\Api\Controllers\BaseController;
use Modules\Video\Models\Video;
use Request;

class VideoController extends BaseController
{
    public function __construct()
    {
    }

    public function transform($video)
    {
        $attributes = $video->getAttributes();
        $attributes['images'] = $video->images()->transform(function ($item) use ($video) {
            return [
                'id' => $item->id,
                'title' => !empty($item->title) ?: $video->title,
                'url' => get_image_url($item->url),
                'summary' => $item->summary,
            ];
        });
        $attributes['comment_count'] = $video->comment_count;
        $attributes['favorite_count'] = $video->favorite_count;
        $attributes['follow_count'] = $video->follow_count;
        $attributes['like_count'] = $video->like_count;
        $attributes['click_count'] = $video->click_count;
        $attributes['created_at'] = empty($video->created_at) ? '' : $video->created_at->toDateTimeString();
        $attributes['updated_at'] = empty($video->updated_at) ? '' : $video->updated_at->toDateTimeString();
        return $attributes;
    }

    /**
     * @SWG\Get(
     *   path="/videos",
     *   summary="获取媒资列表",
     *   tags={"/videos 媒资"},
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

        $key = "video-list-$site_id-$category_id-$page_size-$page";

        return cache_remember($key, 1, function () use ($site_id, $page_size, $page, $category_id) {
            $videos = Video::with('items')
                ->where('site_id', $site_id)
                ->where('category_id', $category_id)
                ->where('state', Video::STATE_PUBLISHED)
                ->orderBy('sort', 'desc')
                ->skip(($page - 1) * $page_size)
                ->limit($page_size)
                ->get();

            $videos->transform(function ($video) {
                return $this->transform($video);
            });

            return $this->responseSuccess($videos);
        });
    }

    /**
     * @SWG\Get(
     *   path="/videos/search",
     *   summary="搜索媒资",
     *   tags={"/videos 媒资"},
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

        $videos = Video::with('items')
            ->where('site_id', $site_id)
            ->where('title', 'like', '%' . $title . '%')
            ->where('state', Video::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $videos->transform(function ($video) {
            return $this->transform($video);
        });

        return $this->responseSuccess($videos);
    }

    /**
     * @SWG\Get(
     *   path="/videos/info",
     *   summary="获取媒资信息",
     *   tags={"/videos 媒资"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="媒资ID", type="string"),
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

        Video::click($id);

        $key = "videos-info-$id";

        return cache_remember($key, 1, function () use ($id) {
            $video = Video::find($id);
            if (empty($video)) {
                return $this->responseFail('此ID不存在');
            }

            return $this->responseSuccess($this->transform($video));
        });
    }

    /**
     * @SWG\Get(
     *   path="/videos/detail",
     *   summary="获取媒资详情页",
     *   tags={"/videos 媒资"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="媒资ID", type="string"),
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

        Video::click($id);

        $key = "videos-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $video = Video::findOrFail($id);
            $site = $video->site;
            $theme = $video->site->mobile_theme->name;
            $video->content = replace_content_url($video->content);
            return view("$theme.videos.detail", compact('site', 'video'))->__toString();
        });
    }

    /**
     * @SWG\Get(
     *   path="/videos/share",
     *   summary="获取媒资分享页",
     *   tags={"/videos 媒资"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="媒资ID", type="string"),
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

        Video::click($id);

        $key = "videos-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $video = Video::findOrFail($id);
            $site = $video->site;
            $theme = $video->site->mobile_theme->name;
            $video->content = replace_content_url($video->content);
            $share = 1;
            return view("$theme.videos.detail", compact('site', 'video', 'share'))->__toString();
        });
    }
}