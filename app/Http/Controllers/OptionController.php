<?php

namespace App\Http\Controllers;

use App\Models\Option;
use Gate;
use Request;
use Response;

class OptionController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        if (Gate::denies('@option')) {
            $this->middleware('deny403');
        }

        return view('admin.options.index');
    }

    public function save($id)
    {
        $input = Request::all();
        $value = Request::get('value');

        $input['value'] = is_array($value) ? implode(',', $value) : $value;

        $option = Option::find($id);
        if ($option == null) {
            return;
        }

        if (isset($input['type']) && $input['type'] != $option->type) {
            $input['value'] = '';
        }

        $option->update($input);
    }

    public function table()
    {
        $options = Option::owns()
            ->get();

        $options->transform(function ($option) {
            return [
                'id' => $option->id,
                'code' => $option->code,
                'name' => $option->name,
                'value' => $option->value,
                'type' => $option->type,
                'type_name' => $option->typeName(),
                'option' => $option->option,
                'site_name' => $option->site->title,
            ];
        });

        $ds = new \stdClass();
        $ds->data = $options;

        return Response::json($ds);
    }
}
