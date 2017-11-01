<?php

namespace Modules\Survey\Web;

use App\Http\Controllers\BaseController;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyItem;
use Request;
use Response;

class SurveyItemController extends BaseController
{
    public function show($survey_id)
    {
        return view('survey.views.items', compact('survey_id'));
    }

    public function update($id)
    {
        $input = Request::all();
        $item = SurveyItem::find($id);
        $item->count = $input['count'];

        if ($item == null) {
            return;
        }
        $item->save();
    }

    public function table($survey_id)
    {
        $survey = Survey::with('subjects')->find($survey_id);

        $subjects = $survey->subjects()->orderBy('id', 'desc')->get();

        $titles_item = [];
        foreach ($subjects as $k => $subject) {
            $titles = $subject->items()->orderBy('id', 'desc')->get();

            $amount = $subject->items->sum('count');

            $titles->transform(function ($title) use ($subject, $amount) {
                return [
                    'id' => $title->id,
                    'survey_id' => $title->refer_id,
                    'subject' => $subject->title,
                    'title' => $title->title,
                    'percent' => $amount == 0 ? 0 : round(($title->count / $amount) * 100) . '%',
                    'count' => $title->count,
                    'sort' => $title->sort,
                ];
            });

            $titles_item[] = $titles->toArray();
        }
        $titles = [];
        foreach ($titles_item as $item) {
            foreach ($item as $value) {
                $titles[] = $value;
            }
        }
        $ds = new \stdClass();
        $ds->data = $titles;
        return Response::json($ds);
    }


}
