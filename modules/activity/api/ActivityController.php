<?php

namespace Modules\Activity\Api;

use App\Api\Controllers\BaseController;
use App\Models\Comment;
use DB;
use Exception;
use Modules\Activity\Models\Activity;
use Modules\Activity\Models\ActivityData;
use Request;

class ActivityController extends BaseController
{
    public function __construct()
    {
    }

    public function transform($activity)
    {
        $attributes = $activity->getAttributes();
        $attributes['images'] = $activity->images()->transform(function ($item) use ($activity) {
            return [
                'id' => $item->id,
                'title' => !empty($item->title) ?: $activity->title,
                'url' => get_image_url($item->url),
                'summary' => $item->summary,
            ];
        });
        isset($attributes['comments_count']) ?: $attributes['comments_count'] = $activity->comments_count;
        isset($attributes['favorites_count']) ?: $attributes['favorites_count'] = $activity->favorites_count;
        isset($attributes['follows_count']) ?: $attributes['follows_count'] = $activity->follows_count;
        $attributes['likes_count'] = $activity->likes_count;
        $attributes['clicks_count'] = $activity->clicks_count;
        $attributes['created_at'] = empty($activity->created_at) ? '' : $activity->created_at->toDateTimeString();
        $attributes['updated_at'] = empty($activity->updated_at) ? '' : $activity->updated_at->toDateTimeString();
        return $attributes;
    }

    /**
     * @SWG\Get(
     *   path="/activities",
     *   summary="获取活动列表",
     *   tags={"/activities 活动"},
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

        $key = "activity-list-$site_id-$category_id-$page_size-$page";

        return cache_remember($key, 1, function () use ($site_id, $page_size, $page, $category_id) {
            $activities = Activity::with('items')
                ->withCount(['comments' => function ($query) {
                    $query->where('state', Comment::STATE_PUBLISHED);
                }])
                ->withCount('favorites')
                ->withCount('follows')
                ->where('site_id', $site_id)
                ->where('category_id', $category_id)
                ->where('state', Activity::STATE_PUBLISHED)
                ->orderBy('sort', 'desc')
                ->skip(($page - 1) * $page_size)
                ->limit($page_size)
                ->get();

            $activities->transform(function ($activity) {
                return $this->transform($activity);
            });

            return $this->responseSuccess($activities);
        });
    }

    /**
     * @SWG\Get(
     *   path="/activities/commit",
     *   summary="提交活动",
     *   tags={"/activities 活动"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="integer"),
     *   @SWG\Parameter(name="activity_id", in="query", required=true, description="活动ID", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Parameter(name="person_name", in="query", required=true, description="姓名", type="string"),
     *   @SWG\Parameter(name="person_mobile", in="query", required=true, description="手机号", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="提交成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function commit()
    {
        $site_id = Request::get('site_id');
        $activity_id = Request::get('activity_id');
        $person_name = Request::get('person_name') ?: '';
        $person_mobile = Request::get('person_mobile') ?: '';

        try {
            $member = \JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        //判断是否存在
        $activity = Activity::find($activity_id);
        if (empty($activity)) {
            return $this->responseError('此活动ID不存在');
        }

        //判断是否已有活动记录
        $data = ActivityData::where('activity_id', $activity_id)
            ->where('member_id', $member->id)
            ->first();
        if (!empty($data)) {
            return $this->responseError('您已参与活动了');
        }

        //判断是否有填报名信息
        if (empty($person_name)) {
            return $this->responseError('请填写姓名');
        }

        if (!preg_match("/1[34578]{1}\d{9}$/", $person_mobile)) {
            return $this->responseError('请输入正确的手机号');
        }

        //保存数据
        DB::beginTransaction();
        try {
            //增加活动数据记录
            $data = new ActivityData();
            $data->activity_id = $activity_id;
            $data->person_name = $person_name;
            $data->person_mobile = $person_mobile;
            $data->member_id = $member->id;
            $data->ip = Request::getClientIp();
            $data->save();
            //增加活动数
            Activity::find($activity_id)->increment('amount');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            DB::commit();
        }

        return $this->responseSuccess();
    }

    /**
     * @SWG\Get(
     *   path="/activities/search",
     *   summary="搜索活动",
     *   tags={"/activities 活动"},
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

        $activities = Activity::with('items')
            ->withCount(['comments' => function ($query) {
                $query->where('state', Comment::STATE_PUBLISHED);
            }])
            ->withCount('favorites')
            ->withCount('follows')
            ->where('site_id', $site_id)
            ->where('title', 'like', '%' . $title . '%')
            ->where('state', Activity::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $activities->transform(function ($activity) {
            return $this->transform($activity);
        });

        return $this->responseSuccess($activities);
    }

    /**
     * @SWG\Get(
     *   path="/activities/info",
     *   summary="获取活动信息",
     *   tags={"/activities 活动"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="活动ID", type="string"),
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

        Activity::click($id);

        $key = "activities-info-$id";

        return cache_remember($key, 1, function () use ($id) {
            $activity = Activity::find($id);
            if (empty($activity)) {
                return $this->responseFail('此ID不存在');
            }

            return $this->responseSuccess($this->transform($activity));
        });
    }

    /**
     * @SWG\Get(
     *   path="/activities/detail",
     *   summary="获取活动详情页",
     *   tags={"/activities 活动"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="活动ID", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
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

        Activity::click($id);

        $key = "activities-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $activity = Activity::findOrFail($id);
            $site = $activity->site;
            $theme = $activity->site->mobile_theme->name;
            $activity->content = replace_content_url($activity->content);
            if (Request::has('token')) {
                try {
                    $member = \JWTAuth::parseToken()->authenticate();
                } catch (Exception $e) {
                    return $this->responseError('无效的token,请重新登录');
                }
            }
            return view("$theme.activities.detail", compact('site', 'activity', 'member'))->__toString();
        });

    }

    /**
     * @SWG\Get(
     *   path="/activities/share",
     *   summary="获取活动分享页",
     *   tags={"/activities 活动"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="活动ID", type="string"),
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

        Activity::click($id);

        $key = "activities-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $activity = Activity::findOrFail($id);
            $site = $activity->site;
            $theme = $activity->site->mobile_theme->name;
            $activity->content = replace_content_url($activity->content);
            $share = 1;
            return view("$theme.activities.detail", compact('site', 'activity', 'share'))->__toString();
        });
    }
}