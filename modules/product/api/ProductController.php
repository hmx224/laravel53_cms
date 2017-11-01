<?php

namespace Modules\Product\Api;

use App\Api\Controllers\BaseController;
use App\Models\Comment;
use Modules\Product\Models\Product;
use Request;

class ProductController extends BaseController
{
    public function __construct()
    {
    }

    public function transform($product)
    {
        $attributes = $product->getAttributes();
        $attributes['images'] = $product->images()->transform(function ($item) use ($product) {
            return [
                'id' => $item->id,
                'title' => !empty($item->title) ?: $product->title,
                'url' => get_image_url($item->url),
                'summary' => $item->summary,
            ];
        });
        isset($attributes['comments_count']) ? : $attributes['comments_count']= $product->comments_count;
        isset($attributes['favorites_count']) ? : $attributes['favorites_count'] = $product->favorites_count;
        isset($attributes['follows_count']) ? : $attributes['follows_count'] = $product->follows_count;
        $attributes['likes_count'] = $product->likes_count;
        $attributes['clicks_count'] = $product->clicks_count;
        $attributes['created_at'] = empty($product->created_at) ? '' : $product->created_at->toDateTimeString();
        $attributes['updated_at'] = empty($product->updated_at) ? '' : $product->updated_at->toDateTimeString();
        return $attributes;
    }

    /**
     * @SWG\Get(
     *   path="/products",
     *   summary="获取商品列表",
     *   tags={"/products 商品"},
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

        $key = "product-list-$site_id-$category_id-$page_size-$page";

        return cache_remember($key, 1, function () use ($site_id, $page_size, $page, $category_id) {
            $products = Product::with('items')
                ->withCount(['comments' => function ($query) {
                    $query->where('state', Comment::STATE_PUBLISHED);
                }])
                ->withCount('favorites')
                ->withCount('follows')
                ->where('site_id', $site_id)
                ->where('category_id', $category_id)
                ->where('state', Product::STATE_PUBLISHED)
                ->orderBy('sort', 'desc')
                ->skip(($page - 1) * $page_size)
                ->limit($page_size)
                ->get();

            $products->transform(function ($product) {
                return $this->transform($product);
            });

            return $this->responseSuccess($products);
        });
    }

    /**
     * @SWG\Get(
     *   path="/products/search",
     *   summary="搜索商品",
     *   tags={"/products 商品"},
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

        $products = Product::with('items')
            ->withCount(['comments' => function ($query) {
                $query->where('state', Comment::STATE_PUBLISHED);
            }])
            ->withCount('favorites')
            ->withCount('follows')
            ->where('site_id', $site_id)
            ->where('title', 'like', '%' . $title . '%')
            ->where('state', Product::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $products->transform(function ($product) {
            return $this->transform($product);
        });

        return $this->responseSuccess($products);
    }

    /**
     * @SWG\Get(
     *   path="/products/info",
     *   summary="获取商品信息",
     *   tags={"/products 商品"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="商品ID", type="string"),
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

        Product::click($id);

        $key = "products-info-$id";

        return cache_remember($key, 1, function () use ($id) {
            $product = Product::find($id);
            if (empty($product)) {
                return $this->responseFail('此ID不存在');
            }

            return $this->responseSuccess($this->transform($product));
        });
    }

    /**
     * @SWG\Get(
     *   path="/products/detail",
     *   summary="获取商品详情页",
     *   tags={"/products 商品"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="商品ID", type="string"),
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

        Product::click($id);

        $key = "products-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $product = Product::findOrFail($id);
            $site = $product->site;
            $theme = $product->site->mobile_theme->name;
            $product->content = replace_content_url($product->content);
            return view("$theme.product.detail", compact('site', 'product'))->__toString();
        });
    }

    /**
     * @SWG\Get(
     *   path="/products/share",
     *   summary="获取商品分享页",
     *   tags={"/products 商品"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="商品ID", type="string"),
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

        Product::click($id);

        $key = "products-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $product = Product::findOrFail($id);
            $site = $product->site;
            $theme = $product->site->mobile_theme->name;
            $product->content = replace_content_url($product->content);
            $share = 1;
            return view("$theme.product.detail", compact('site', 'product', 'share'))->__toString();
        });
    }
}