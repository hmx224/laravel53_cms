<?php

namespace Modules\Article\Api;

use App\Api\Controllers\BaseController;
use App\Models\Comment;
use Modules\Article\Models\Article;
use Request;

class ArticleController extends BaseController
{
    public function __construct()
    {
    }

    public function transform($article)
    {
        $attributes = $article->getAttributes();
        $attributes['images'] = $article->images()->transform(function ($item) use ($article) {
            return [
                'id' => $item->id,
                'title' => !empty($item->title) ?: $article->title,
                'url' => get_image_url($item->url),
                'summary' => $item->summary,
            ];
        });
        isset($attributes['comments_count']) ?: $attributes['comments_count'] = $article->comments_count;
        isset($attributes['favorites_count']) ?: $attributes['favorites_count'] = $article->favorites_count;
        isset($attributes['follows_count']) ?: $attributes['follows_count'] = $article->follows_count;
        $attributes['likes_count'] = $article->likes_count;
        $attributes['clicks_count'] = $article->clicks_count;
        $attributes['created_at'] = empty($article->created_at) ? '' : $article->created_at->toDateTimeString();
        $attributes['updated_at'] = empty($article->updated_at) ? '' : $article->updated_at->toDateTimeString();
        return $attributes;
    }

    /**
     * @SWG\Get(
     *   path="/articles",
     *   summary="获取文章列表",
     *   tags={"/articles 文章"},
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

        $key = "article-list-$site_id-$category_id-$page_size-$page";

        return cache_remember($key, 1, function () use ($site_id, $tag, $page_size, $page, $category_id) {
            $articles = Article::with('items', 'tags')
                ->withCount(['comments' => function ($query) {
                    $query->where('state', Comment::STATE_PUBLISHED);
                }])
                ->withCount('favorites')
                ->withCount('follows')
                ->where('site_id', $site_id)
                ->where('category_id', $category_id)
                ->where('state', Article::STATE_PUBLISHED)
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

            $articles->transform(function ($article) {
                return $this->transform($article);
            });

            return $this->responseSuccess($articles);
        });
    }

    /**
     * @SWG\Get(
     *   path="/articles/search",
     *   summary="搜索文章",
     *   tags={"/articles 文章"},
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

        $articles = Article::with('items')
            ->withCount(['comments' => function ($query) {
                $query->where('state', Comment::STATE_PUBLISHED);
            }])
            ->withCount('favorites')
            ->withCount('follows')
            ->where('site_id', $site_id)
            ->where('title', 'like', '%' . $title . '%')
            ->where('state', Article::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->skip(($page - 1) * $page_size)
            ->limit($page_size)
            ->get();

        $articles->transform(function ($article) {
            return $this->transform($article);
        });

        return $this->responseSuccess($articles);
    }

    /**
     * @SWG\Get(
     *   path="/articles/info",
     *   summary="获取文章信息",
     *   tags={"/articles 文章"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="文章ID", type="string"),
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

        Article::click($id);

        $key = "articles-info-$id";

        return cache_remember($key, 1, function () use ($id) {
            $article = Article::find($id);
            if (empty($article)) {
                return $this->responseFail('此ID不存在');
            }

            return $this->responseSuccess($this->transform($article));
        });
    }

    /**
     * @SWG\Get(
     *   path="/articles/detail",
     *   summary="获取文章详情页",
     *   tags={"/articles 文章"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="文章ID", type="string"),
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

        Article::click($id);

        $key = "articles-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $article = Article::findOrFail($id);
            $site = $article->site;
            $theme = $article->site->mobile_theme->name;
            $article->content = replace_content_url($article->content);
            return view("$theme.article.detail", compact('site', 'article'))->__toString();
        });
    }

    /**
     * @SWG\Get(
     *   path="/articles/share",
     *   summary="获取文章分享页",
     *   tags={"/articles 文章"},
     *   @SWG\Parameter(name="id", in="query", required=true, description="文章ID", type="string"),
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

        Article::click($id);

        $key = "articles-detail-$id";

        return cache_remember($key, 1, function () use ($id) {
            $article = Article::findOrFail($id);
            $site = $article->site;
            $theme = $article->site->mobile_theme->name;
            $article->content = replace_content_url($article->content);
            $share = 1;
            return view("$theme.article.detail", compact('site', 'article', 'share'))->__toString();
        });
    }
}