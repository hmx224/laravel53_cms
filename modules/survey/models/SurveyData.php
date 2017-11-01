<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyData extends Model
{
    protected $table = 'survey_data';

    protected $fillable = [
        'survey_id',
        'survey_item_ids',
        'comment',
        'person_name',
        'person_mobile',
        'member_name',
        'ip',
    ];
}
