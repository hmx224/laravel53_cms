<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Config;
use Exception;
use Request;
use Response;

class FileController extends Controller
{
    const ALLOW_EXTENSIONS = ['gif', 'jpeg', 'jpg', 'png', 'webp', 'mp4', 'mpg', 'mpeg', 'avi', 'wav', 'mp3', 'amr', 'caf', 'apk'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 上传文件
     */
    public function upload()
    {
        $file = current(Request::allFiles());
        if (empty($file)) {
            return Response::json([
                'status_code' => 404,
                'message' => '请上传文件',
                'data' => [],
            ]);
        }
        if (is_array($file)) {
            $file = current($file);
        }
        try {
            if (Request::get('type') == 'video') {
                $url = $this->uploadVideo($file);

                return Response::json([
                    'status_code' => 200,
                    'message' => 'success',
                    'data' => $url,
                    'initialPreview' => [
                        '<video height="240" controls="controls" src="' . $url . '"></video>',
                    ],
                    'initialPreviewConfig' => [
                        ['key' => time(), 'video_url' => $url],
                    ],
                    'append' => true,
                ]);
            } else if (Request::get('type') == 'audio') {
                $url = $this->uploadAudio($file);

                return Response::json([
                    'status_code' => 200,
                    'message' => 'success',
                    'data' => $url,
                    'initialPreview' => [
                        '<audio controls="controls" src="' . $url . '"></video>',
                    ],
                    'initialPreviewConfig' => [
                        ['key' => time(), 'audio_url' => $url],
                    ],
                    'append' => true,
                ]);
            } else if (Request::get('type') == 'image') {
                $url = $this->uploadImage($file);

                if (Request::has('CKEditorFuncNum')) {
                    $funcNum = $_GET['CKEditorFuncNum'];
                    echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url');</script>";
                } else {
                    return Response::json([
                        'status_code' => 200,
                        'message' => 'success',
                        'data' => $url,
                        'initialPreview' => [
                            '<img height="240" src="' . $url . '" class="kv-preview-data file-preview-image">',
                        ],
                        'initialPreviewConfig' => [
                            ['key' => time(), 'image_url' => $url],
                        ],
                        'append' => true,
                    ]);
                }
            } else if (Request::get('type') == 'file') {
                $url = $this->uploadFile($file);

                return Response::json([
                    'status_code' => 200,
                    'message' => 'success',
                    'data' => $url,
                ]);
            }
        } catch (Exception $e) {
            return Response::json([
                'status_code' => 500,
                'message' => 'failure',
                'data' => $e,
            ]);
        }
    }

    public function uploadImage($file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, static::ALLOW_EXTENSIONS)) {
            return $this->responseFail('不允许上传此类型文件');
        }

        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $time = Carbon::now()->format('YmdHis');

        $relativePath = Config::get('site.upload.image_path') . '/' . $year . '/' . $month . $day . '/';
        $uploadPath = public_path() . $relativePath;
        $filename = $time . mt_rand(100, 999) . '.' . $extension;
        $targetFile = $uploadPath . $filename;

        $file->move($uploadPath, $targetFile);

        $url = Config::get('site.upload.url_prefix') . $relativePath . $filename;

        return $url;
    }

    public function uploadVideo($file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, static::ALLOW_EXTENSIONS)) {
            return $this->responseFail('不允许上传此类型文件');
        }

        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $time = Carbon::now()->format('YmdHis');

        $relativePath = Config::get('site.upload.video_path') . '/' . $year . '/' . $month . $day . '/';
        $uploadPath = public_path() . $relativePath;
        $filename = $time . mt_rand(100, 999) . '.' . $extension;
        $targetFile = $uploadPath . $filename;

        $file->move($uploadPath, $targetFile);

        $url = Config::get('site.upload.url_prefix') . $relativePath . $filename;

        //$this->preload('http://cmscdn.asia-cloud.com' . $url);

        return $url;
    }

    public function uploadAudio($file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, static::ALLOW_EXTENSIONS)) {
            return $this->responseFail('不允许上传此类型文件');
        }

        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $time = Carbon::now()->format('YmdHis');

        $relativePath = Config::get('site.upload.audio_path') . '/' . $year . '/' . $month . $day . '/';
        $uploadPath = public_path() . $relativePath;
        $filename = $time . mt_rand(100, 999) . '.' . $extension;
        $targetFile = $uploadPath . $filename;

        $file->move($uploadPath, $targetFile);

        $url = Config::get('site.upload.url_prefix') . $relativePath . $filename;

        //$this->preload('http://cmscdn.asia-cloud.com' . $url);

        return $url;
    }

    public function uploadFile($file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, static::ALLOW_EXTENSIONS)) {
            return $this->responseFail('不允许上传此类型文件');
        }

        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $day = Carbon::now()->format('d');
        $time = Carbon::now()->format('YmdHis');

        $relativePath = Config::get('site.upload.other_path') . '/' . $year . '/' . $month . $day . '/';
        $uploadPath = public_path() . $relativePath;
        $filename = $time . mt_rand(100, 999) . '.' . $extension;
        $targetFile = $uploadPath . $filename;

        $file->move($uploadPath, $targetFile);

        $url = Config::get('site.upload.url_prefix') . $relativePath . $filename;

        return $url;
    }

    public function delete()
    {
        return Response::json([
            'status_code' => 200,
            'message' => 'success'
        ]);
    }
}
