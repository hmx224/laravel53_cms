<?php

namespace Modules\Survey\Models;

use App\Models\Item;

class Subject extends Item
{
    protected $table = 'items';

    public function items()
    {
        return $this->morphMany(SurveyItem::class, 'refer');
    }

}
