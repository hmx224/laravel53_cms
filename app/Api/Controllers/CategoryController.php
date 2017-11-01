<?php

namespace App\Api\Controllers;

use App\Models\Category;
use Request;

class CategoryController extends BaseController
{
    public function transform($category)
    {
        $attributes = $category->getAttributes();

        $attributes['image_url'] = get_image_url($category->image_url);
        $attributes['cover_url'] = get_image_url($category->cover_url);

        return $attributes;
    }

    /**
     * @SWG\Get(
     *   path="/categories",
     *   summary="获取栏目列表",
     *   tags={"/categories 栏目"},
     *   @SWG\Parameter(name="site_id", in="query", required=true, description="站点ID", type="integer"),
     *   @SWG\Parameter(name="parent_id", in="query", required=true, description="上级栏目ID", type="integer"),
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
        $page_size = Request::get('page_size') ? Request::get('page_size') : 20;
        $page = Request::get('page') ? Request::get('page') : 1;
        $site_id = Request::get('site_id') ? Request::get('site_id') : 1;
        $parent_id = Request::get('parent_id');

        $categories = Category::where('site_id', $site_id)
            ->where('state', Category::STATE_ENABLED)
            ->where('parent_id', $parent_id)
            ->orderBy('sort')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $categories->transform(function ($category) {
            return $this->transform($category);
        });

        return $this->responseSuccess($categories);
    }

    /**
     * @SWG\Get(
     *   path="/categories/detail",
     *   summary="获取栏目详情页",
     *   tags={"/categories 栏目"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="栏目ID", type="string"),
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
        $site_id = Request::get('site_id') ? Request::get('site_id') : 1;
        $id = Request::get('id');

        $key = "category-detail-$site_id-$id";
        return cache_remember($key, 1, function () use ($id) {
            $category = Category::findOrFail($id);
            $category->content = replace_content_url($category->content);
            return view('mobile.categories.detail', compact('category'))->__toString();
        });
    }

    /**
     * @SWG\Get(
     *   path="/categories/info",
     *   summary="获取栏目信息",
     *   tags={"/categories 栏目"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="栏目ID", type="string"),
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

        $key = "Category-info-$id";

        return cache_remember($key, 1, function () use ($id) {
            $category = Category::find($id);
            if (empty($category)) {
                return $this->responseFail('此ID不存在');
            }

            return $this->responseSuccess($this->transform($category));
        });
    }

}