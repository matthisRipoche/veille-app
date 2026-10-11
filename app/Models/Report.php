<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['date', 'title', 'summary', 'content_md'];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }

    /** Date au format AAAA-MM-JJ, utilisée dans les URL. */
    public function slug(): string
    {
        return $this->date->toDateString();
    }
}
