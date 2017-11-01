<?php

namespace Modules\Product\Web;

use App\Events\UserLogEvent;
use App\Http\Controllers\BaseController;
use App\Jobs\PublishPage;
use App\Models\Category;
use App\Models\Domain;
use App\Models\Module;
use App\Models\UserLog;
use Auth;
use Carbon\Carbon;
use Gate;
use Modules\Product\Models\Product;
use Request;
use Response;

/**
 * 商品
 */
class ProductController extends BaseController
{
    protected $base_url = '/admin/products';
    protected $view_path = 'product.views';
    protected $module;

    public function __construct()
    {
        $this->module = Module::where('name', 'Product')->first();
    }

    public function show(Domain $domain, $id)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $product = Product::find($id);
        if (empty($product)) {
            return abort(404);
        }
        $product->incrementClick();

        return view($domain->theme->name . '.product.detail', ['site' => $domain->site, 'product' => $product]);
    }

    public function slug(Domain $domain, $slug)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $product = Product::where('slug', $slug)
            ->first();
        if (empty($product)) {
            return abort(404);
        }
        $product->incrementClick();

        return view($domain->theme->name . '.product.detail', ['site' => $domain->site, 'product' => $product]);
    }

    public function lists(Domain $domain)
    {
        if (empty($domain->site)) {
            return abort(501);
        }

        $products = Product::where('state', Product::STATE_PUBLISHED)
            ->orderBy('sort', 'desc')
            ->get();

        return view($domain->theme->name . '.product.index', ['site' => $domain->site, 'module' => $this->module, 'products' => $products]);
    }

    public function index()
    {
        if (Gate::denies('@product')) {
            return abort(403);
        }

        $module = Module::transform($this->module->id);

        return view($this->view_path . '.index', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function create()
    {
        if (Gate::denies('@product-create')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        return view('admin.contents.create', ['module' => $module, 'base_url' => $this->base_url]);
    }

    public function edit($id)
    {
        if (Gate::denies('@product-edit')) {
            \Session::flash('flash_warning', '无此操作权限');
            return redirect()->back();
        }

        $module = Module::transform($this->module->id);

        $product = Product::find($id);
        $product->images = null;
        $product->videos = null;
        $product->audios = null;
        $product->tags = $product->tags()->pluck('name')->toArray();

        return view('admin.contents.edit', ['module' => $module, 'content' => $product, 'base_url' => $this->base_url, 'back_url' => $this->base_url . '?category_id=' . $product->category_id]);
    }

    public function store()
    {
        $input = Request::all();
        $input['site_id'] = Auth::user()->site_id;
        $input['user_id'] = Auth::user()->id;

        $validator = Module::validate($this->module, $input);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product = Product::stores($input);

        event(new UserLogEvent(UserLog::ACTION_CREATE . '商品', $product->id, $this->module->model_class));

        \Session::flash('flash_success', '添加成功');
        return redirect($this->base_url . '?category_id=' . $product->category_id);
    }

    public function update($id)
    {
        $input = Request::all();

        $validator = Module::validate($this->module, $input);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $product = Product::updates($id, $input);

        event(new UserLogEvent(UserLog::ACTION_UPDATE . '商品', $product->id, $this->module->model_class));

        \Session::flash('flash_success', '修改成功!');
        return redirect($this->base_url . '?category_id=' . $product->category_id);
    }

    public function comments($refer_id)
    {
        $refer_type = $this->module->model_class;
        return view('admin.comments.list', compact('refer_id', 'refer_type'));
    }

    public function save($id)
    {
        $product = Product::find($id);

        if (empty($product)) {
            return;
        }

        $product->update(Request::all());
    }

    public function sort()
    {
        return Product::sort();
    }

    public function top($id)
    {
        $product = Product::find($id);
        $product->top = !$product->top;
        $product->save();
    }

    public function tag($id)
    {
        $tag = request('tag');
        $product = Product::find($id);
        if ($product->tags()->where('name', $tag)->exists()) {
            $product->tags()->where('name', $tag)->delete();
        } else {
            $product->tags()->create([
                'site_id' => $product->site_id,
                'name' => $tag,
                'sort' => strtotime(Carbon::now()),
            ]);
        }
    }

    public function state()
    {
        $input = request()->all();
        Product::state($input);

        $ids = $input['ids'];
        $stateName = Product::getStateName($input['state']);

        //记录日志
        foreach ($ids as $id) {
            event(new UserLogEvent('变更' . '商品' . UserLog::ACTION_STATE . ':' . $stateName, $id, $this->module->model_class));
        }

        //发布页面
        $site = auth()->user()->site;
        if ($input['state'] == Product::STATE_PUBLISHED) {
            foreach ($ids as $id) {
                $this->dispatch(new PublishPage($site, $this->module, $id));
            }
        }
    }

    public function table()
    {
        return Product::table();
    }

    public function categories()
    {
        return Response::json(Category::tree('', 0, $this->module->id, false));
    }
}
