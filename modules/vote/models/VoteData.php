<?php

namespace Modules\Vote\Models;

use Illuminate\Database\Eloquent\Model;

class VoteData extends Model
{
    protected $table = 'vote_data';

    protected $fillable = [
        'vote_id',
        'vote_item_ids',
        'comment',
        'person_name',
        'person_mobile',
        'member_id',
        'ip',
    ];
}
