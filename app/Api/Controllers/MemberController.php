<?php

namespace App\Api\Controllers;

use App\Libraries\Sms;
use App\Models\Favorite;
use App\Models\Member;
use App\Models\Message;
use Cache;
use Carbon\Carbon;
use Exception;
use Request;

class MemberController extends BaseController
{
    /**
     * @SWG\Get(
     *   path="/members/login",
     *   summary="会员登录",
     *   tags={"/members 会员"},
     *   @SWG\Parameter(name="member_name", in="query", required=true, description="会员名", type="string"),
     *   @SWG\Parameter(name="password", in="query", required=false, description="密码", type="string"),
     *   @SWG\Parameter(name="captcha", in="query", required=false, description="验证码", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="登录成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function login()
    {
        $member_name = Request::get('member_name');
        $password = Request::get('password');
        $captcha = Request::get('captcha');

        $mobile = $member_name;

        if (!preg_match("/1[34578]{1}\d{9}$/", $mobile)) {
            return $this->responseError('请输入正确的手机号', 401);
        }

        try {
            $member = Member::where('name', $member_name)
                ->first();

            if (!empty($password)) {
                if ($member->password != md5(md5($password) . $member->salt)) {
                    return $this->responseError('密码错误', 401);
                }
            }else {
                //比较验证码
                $key = 'captcha_' . $mobile;
                if (Cache::get($key) != $captcha) {
                    return $this->responseError('手机验证码错误', 401);
                }
                //移除验证码
                Cache::forget($key);
            }

            if (!$member) {
                $member = Member::create([
                    'name' => $member_name,
                    'password' => '',
                    'nick_name' => $member_name,
                    'mobile' => $member_name,
                    'avatar_url' => url('/images/avatar_member.png'),
                    'salt' => '',
                    'type' => Member::TYPE_NORMAL,
                    'state' => Member::STATE_ENABLED,
                    'points' => 0,
                    'ip' => get_client_ip(),
                ]);
            }

            if ($member->state == Member::STATE_DISABLED) {
                return $this->responseError('此用户已被禁用', 401);
            }

            //旧token作废
            try {
                \JWTAuth::refresh($member->token);
            } catch (Exception $e) {
            }

            $member->token = \JWTAuth::fromUser($member);
            $member->ip = get_client_ip();
            $member->save();

            unset($member->password);
            return $this->responseSuccess($member);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *   path="/members/exlogin",
     *   summary="第三方登录",
     *   tags={"/members 会员"},
     *   @SWG\Parameter(name="member_name", in="query", required=true, description="会员名", type="string"),
     *   @SWG\Parameter(name="nick_name", in="query", required=true, description="昵称", type="string"),
     *   @SWG\Parameter(name="avatar_url", in="query", required=true, description="头像URL", type="string"),
     *   @SWG\Parameter(name="source", in="query", required=true, description="来源(qq,wx,wb)", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="登录成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function exLogin()
    {
        $member_name = Request::get('member_name');
        $nick_name = Request::get('nick_name');
        $avatar_url = Request::get('avatar_url');
        $source = Request::get('source');

        if (strlen($member_name) <= 11) {
            return $this->responseError('此用户不存在', 401);
        }

        $member = Member::where('name', $member_name)
            ->first();

        //保存本地会员数据
        if ($member) {
            if ($member->state == Member::STATE_DISABLED) {
                return $this->responseError('您的账户已被禁用', 401);
            }

            //旧token作废
            try {
                \JWTAuth::refresh($member->token);
            } catch (Exception $e) {
            }
        } else {
            $member = Member::create([
                'name' => $member_name,
                'nick_name' => $nick_name,
                'avatar_url' => $avatar_url,
                'type' => Member::TYPE_NORMAL,
                'source' => $source,
                'state' => Member::STATE_ENABLED,
                'points' => 0,
                'ip' => get_client_ip(),
            ]);
        }

        $member->token = \JWTAuth::fromUser($member);
        $member->ip = get_client_ip();
        $member->save();

        unset($member->password);
        return $this->responseSuccess($member);
    }

    /**
     * @SWG\Get(
     *   path="/members/register",
     *   summary="会员注册",
     *   tags={"/members 会员"},
     *   @SWG\Parameter(name="member_name", in="query", required=true, description="会员名", type="string"),
     *   @SWG\Parameter(name="password", in="query", required=true, description="密码", type="string"),
     *   @SWG\Parameter(name="captcha", in="query", required=true, description="验证码", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="注册成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function register()
    {
        $member_name = Request::get('member_name');
        $password = Request::get('password');
        $captcha = Request::get('captcha');

        try {
            //比较验证码
            $key = 'captcha_' . $member_name;
            if (Cache::get($key) != $captcha) {
                throw new Exception('手机验证码错误', -1);
            }

            $member = Member::where('name', $member_name)
                ->first();
            if ($member) {
                return $this->responseError('用户名已存在');
            }

            $salt = str_rand();

            $member = Member::create([
                'name' => $member_name,
                'password' => md5(md5($password) . $salt),
                'nick_name' => $member_name,
                'mobile' => $member_name,
                'avatar_url' => url('/images/avatar_member.png'),
                'salt' => $salt,
                'type' => Member::TYPE_NORMAL,
                'state' => Member::STATE_ENABLED,
                'points' => 0,
                'ip' => get_client_ip(),
            ]);

            //移除验证码
            Cache::forget($key);

            return $this->responseSuccess($member);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *   path="/members/info",
     *   summary="获取会员信息",
     *   tags={"/members 会员"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="integer"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="正常"
     *   ),
     *   @SWG\Response(
     *     response="401",
     *     description="无效"
     *   )
     * )
     */
    public function info()
    {
        $site_id = Request::get('site_id') ? Request::get('site_id') : 1;

        try {
            $member = \JWTAuth::parseToken()->authenticate();

            if ($member->token == \JWTAuth::getToken()) {
                $member->messages = Message::count($site_id, $member->id);
                $member->favorites = Favorite::count($site_id, $member->id);

                unset($member->password);
                return $this->responseSuccess($member);
            } else {
                return $this->responseError('无效的token,请重新登录', 401);
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录', 401);
        }
    }

    /**
     * @SWG\Post(
     *   path="/members/avatar",
     *   summary="修改头像",
     *   tags={"/members 会员"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(name="avatar_file", in="formData", required=false, description="图片文件", type="file"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="上传成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到"
     *   )
     * )
     */
    public function avatar()
    {
        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        $file = Request::file('avatar_file');
        if ($file->getSize() > 1024 * 1024) {
            return $this->responseError('头像文件过大');
        }

        $extension = $file->getClientOriginalExtension(); //取得上传文件的扩展名

        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $time = Carbon::now()->format('YmdHis');

        $relativePath = config('site.upload.avatar_path') . '/' . $year . '/' . $month . $day . '/';
        $uploadPath = public_path() . $relativePath;
        $filename = $time . mt_rand(100, 999) . '.' . $extension;
        $targetFile = $uploadPath . $filename;

        $file->move($uploadPath, $targetFile);

        $member->avatar_url = url($relativePath . $filename);
        $member->save();

        return $this->responseSuccess([
            'status_code' => 200,
            'message' => 'success',
            'data' => get_url($member->avatar_url),
        ]);
    }

    /**
     * @SWG\Post(
     *   path="/members/info/nick",
     *   summary="修改会员昵称",
     *   tags={"/members 会员"},
     *   @SWG\Parameter(name="nick_name", in="query", required=true, description="昵称", type="string"),
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="正常"
     *   ),
     *   @SWG\Response(
     *     response="401",
     *     description="无效"
     *   )
     * )
     */
    public function nick()
    {
        $nick_name = Request::get('nick_name');

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        try {
            //检查昵称是否已存在
            $count = Member::where('nick_name', $nick_name)
                ->where('id', '<>', $member->id)
                ->count();
            if ($count > 0) {
                throw new Exception('此昵称已存在', -1);
            }

            $member->nick_name = $nick_name;
            $member->save();

            return $this->responseSuccess($member);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *   path="/members/mobile/captcha",
     *   summary="获取验证码",
     *   tags={"/members 会员"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="integer"),
     *   @SWG\Parameter(name="mobile", in="query", required=true, description="手机号", type="string"),
     *   @SWG\Parameter(name="type", in="query", required=true, description="类型(0:找回密码,1:注册,2:重置密码,3:绑定手机,4:解除绑定手机)", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="正常"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function getCaptcha()
    {
        $site_id = Request::get('site_id') ? Request::get('site_id') : 1;
        $mobile = Request::get('mobile');
        $type = Request::get('type');

        try {
            if (!preg_match("/1[34578]{1}\d{9}$/", $mobile)) {
                throw new Exception('请输入正确的手机号', -1);
            }

            //判断此手机号24小时内发送短信是否过多
            $times = Cache::get('captcha_times_' . $mobile);
            if (!isset($times)) {
                Cache::add('captcha_times_' . $mobile, 1, 24 * 60);
            } elseif ($times >= 20) {
                throw new Exception('您今天发送短信次数过多', -1);
            } else {
                Cache::increment('captcha_times_' . $mobile, 1);
            }

            $sms = new Sms($site_id);
            $code = random(4);
            $content = $sms->getContent($type, $code);
            $ret = $sms->send($mobile, $content);
            if (!$ret) {
                throw new Exception('短信验证码发送失败', -1);
            }

            Cache::add('captcha_' . $mobile, $code, 3);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage());
        }

        return $this->responseSuccess();
    }

    /**
     * @SWG\Get(
     *   path="/members/mobile/bind",
     *   summary="绑定手机号",
     *   tags={"/members 会员"},
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Parameter(name="mobile", in="query", required=true, description="手机号", type="integer"),
     *   @SWG\Parameter(name="captcha", in="query", required=true, description="验证码", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="绑定成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function bindMobile()
    {
        $mobile = Request::get('mobile');
        $captcha = Request::get('captcha');

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        try {
            if (!preg_match("/1[34578]{1}\d{9}$/", $mobile)) {
                throw new Exception('请输入正确的手机号', -1);
            }

            //比较验证码
            $key = 'captcha_' . $mobile;
            if (Cache::get($key) != $captcha) {
                throw new Exception('手机验证码错误', -1);
            }

            $count = Member::where('mobile', $mobile)->count();
            if ($count > 0) {
                throw new Exception('此手机号已经被其他账号绑定', -1);
            }

            $member->mobile = $mobile;
            $member->save();

            //移除验证码
            Cache::forget($key);

            return $this->responseSuccess($member);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *   path="/members/mobile/unbind",
     *   summary="解绑手机号",
     *   tags={"/members 会员"},
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Parameter(name="captcha", in="query", required=true, description="验证码", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="解绑成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function unbindMobile()
    {
        $captcha = Request::get('captcha');

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }
        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        try {
            //比较验证码
            $key = 'captcha_' . $member->mobile;
            if (Cache::get($key) != $captcha) {
                throw new Exception('手机验证码错误', -1);
            }

            $member->mobile = '';
            $member->save();

            //移除验证码
            Cache::forget($key);

            return $this->responseSuccess($member);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *   path="/members/password/change",
     *   summary="修改密码",
     *   tags={"/members 会员"},
     *   @SWG\Parameter(name="token", in="query", required=true, description="token", type="string"),
     *   @SWG\Parameter(name="old_password", in="query", required=true, description="旧密码", type="string"),
     *   @SWG\Parameter(name="new_password", in="query", required=true, description="新密码", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="修改成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function changePassword()
    {
        $old_password = Request::get('old_password');
        $new_password = Request::get('new_password');

        try {
            $member = \JWTAuth::parseToken()->authenticate();
            if (!$member) {
                return $this->responseError('无效的token,请重新登录');
            }

            if ($member->password != md5(md5($old_password) . $member->salt)) {
                return $this->responseError('密码错误', 401);
            }

            $member->password = md5(md5($new_password) . $member->salt);
            $member->save();

        } catch (Exception $e) {
            return $this->responseError('无效的token,请重新登录');
        }

        return $this->responseSuccess();
    }

    /**
     * @SWG\Get(
     *   path="/members/password/reset",
     *   summary="重置密码",
     *   tags={"/members 会员"},
     *   @SWG\Parameter(name="member_name", in="query", required=true, description="会员名", type="string"),
     *   @SWG\Parameter(name="captcha", in="query", required=true, description="验证码", type="string"),
     *   @SWG\Parameter(name="password", in="query", required=true, description="新密码", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="修改成功"
     *   ),
     *   @SWG\Response(
     *     response="404",
     *     description="没有找到路由"
     *   )
     * )
     */
    public function resetPassword()
    {
        $member_name = Request::get('member_name');
        $captcha = Request::get('captcha');
        $password = Request::get('password');

        try {
            if (!preg_match("/1[34578]{1}\d{9}$/", $member_name)) {
                throw new Exception('请输入正确的手机号', -1);
            }

            //比较验证码
            $key = 'captcha_' . $member_name;
            if (Cache::get($key) != $captcha) {
                throw new Exception('手机验证码错误', -1);
            }

            $member = Member::where('name', $member_name)
                ->first();

            //检查用户是否存在
            if (!$member) {
                return $this->responseError('此用户不存在', 401);
            }

            if ($member->state == Member::STATE_DISABLED) {
                return $this->responseError('此用户已被禁用', 401);
            }

            $member->password = md5(md5($password) . $member->salt);
            $member->save();

            //移除验证码
            Cache::forget($key);

            return $this->responseSuccess();
        } catch (Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}