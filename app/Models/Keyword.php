<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Keyword extends Model
{
    protected $fillable = [
        'site_id',
        'name',
        'times',
        ];

    public function scopeOwns($query)
    {
        $query->where('site_id', Auth::user()->site_id);
    }

    public static function post($names)
    {
        foreach ($names as $name) {
            $keyword = Keyword::where('name', $name)->first();
            if (empty($keyword)) {
                Keyword::create([
                    'name' => $name,
                    'times' => 1,
                ]);
            }
            else {
                $keyword->increment('times');
            }
        }
    }
}
