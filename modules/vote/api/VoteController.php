<?php

namespace Modules\Vote\Api;

use App\Api\Controllers\BaseController;
use Modules\Vote\Models\Vote;
use Modules\Vote\Models\VoteData;
use Carbon\Carbon;
use Exception;
use Request;

class VoteController extends BaseController
{

    public function transform($vote)
    {
        $amount = $vote->items->sum('count');

        return [
            'id' => $vote->id,
            'title' => $vote->title,
            'multiple' => $vote->multiple,
            'image_url' => get_image_url($vote->image_url),
            'link' => $vote->link,
            'content' => $vote->content,
            'begin_date' => $vote->begin_date,
            'end_date' => $vote->end_date,
            'amount' => $vote->amount,
            'is_top' => $vote->is_top,
            'state' => $vote->state,
            'items' => $vote->items->transform(function ($item) use ($amount) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'url' => get_image_url($item->url),
                    'count' => $item->count,
                    'percent' => $amount ? round(($item->count / $amount) * 100) . '%' : 0,
                ];
            }),
            'like_count' => $vote->like_count,
            'comment_count' => $vote->comment_count,
        ];
    }

    /**
     * @SWG\Get(
     *   path="/votes",
     *   summary="获取投票列表",
     *   tags={"/votes 投票"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="string"),
     *   @SWG\Parameter(name="page_size", in="query", required=true, description="分页大小", type="integer"),
     *   @SWG\Parameter(name="page", in="query", required=true, description="分页序号", type="integer"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function lists()
    {
        $site_id = Request::get('site_id') ?: 1;
        $page_size = Request::get('page_size') ? min(100, Request::get('page_size')) : 20;
        $page = Request::get('page') ? Request::get('page') : 1;

        //获取结束日期大于等于当前日期的记录
        $votes = Vote::with('items')
            ->where('site_id', $site_id)
            ->where('state', Vote::STATE_PUBLISHED)
            ->where('end_date', '>=', Carbon::now())
            ->orderBy('sort', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $votes->transform(function ($vote) {
            return $this->transform($vote);
        });

        return $this->response([
            'status_code' => 200,
            'message' => 'success',
            'data' => $votes,
        ]);
    }

    /**
     * @SWG\Post(
     *   path="/votes/create",
     *   summary="提交投票",
     *   tags={"/votes 投票"},
     *   @SWG\Parameter(name="vote_id", in="query", required=true, description="投票ID", type="string"),
     *   @SWG\Parameter(name="item_ids", in="query", required=true, description="选项ID", type="array", items={"type": "integer"}),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="投票成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function create()
    {
        $vote_id = Request::get('vote_id');
        $item_ids = Request::get('item_ids');

        //判断会员是否存在
        try {
            $member = \JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        //判断是否存在
        $vote = Vote::find($vote_id);

        if (empty($vote)) {
            return $this->responseError('此投票ID不存在');
        }

        if ($vote->end_date <= Carbon::now()) {
            return $this->responseError('此投票已经结束,不能提交！');
        }

        //判断是否有选项
        if (empty($item_ids)) {
            return $this->responseError('请选择选项');
        }

        //判断是否已有投票记录
        $data = VoteData::where('vote_id', $vote_id)
            ->where('member_id', $member->id)
            ->first();
        if (!empty($data)) {
            return $this->responseError('您已投过票了');
        }

        //增加投票数据记录
        $data = new VoteData();
        $data->vote_id = $vote_id;
        $data->vote_item_ids = $item_ids;
        $data->member_id = $member->id;
        $data->ip = Request::getClientIp();
        $data->save();

        //增加选项投票数
        $vote->items()->whereIn('id', explode(',', $item_ids))->get()->each(function ($item) {
            $item->count += 1;
            $item->save();
        });

        //增加参与数
        $vote->increment('amount');

        return $this->responseSuccess();
    }

    /**
     * @SWG\Get(
     *   path="/votes/detail",
     *   summary="获取投票详情页",
     *   tags={"/votes 投票"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="投票ID", type="string"),
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="查询成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function detail()
    {
        $site_id = Request::get('site_id') ?: 1;
        $id = Request::get('id');

        $vote = Vote::find($id);

        $amount = $vote->items->sum('amount');

        if (empty($vote)) {
            return $this->responseError('此投票ID不存在');
        }

        if (Request::has('token')) {
            try {
                $member = \JWTAuth::parseToken()->authenticate();
            } catch (Exception $e) {
                return view('mobile.401');
            }

            if (!empty($vote->link)) {
                $url = $vote->link;
                if (strpos($url, '?')) {
                    $url .= '&member_id=' . $member->id;
                } else {
                    $url .= '?member_id=' . $member->id;
                }
                return redirect($url);
            }

            return view("mobile.$site_id.votes.detail", compact('vote', 'member', 'amount'));
        } else {
            return view("mobile.$site_id.votes.share", compact('vote', 'amount'));
        }
    }
}