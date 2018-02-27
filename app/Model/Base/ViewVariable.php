<?php

namespace App\Model\Base;

use Illuminate\Database\Eloquent\Model;

class ViewVariable extends Model
{
    public function view()
    {
        $this->belongsTo(View::class);
    }
}
