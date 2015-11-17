<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $table = 'test';
    protected $dateFormat = 'U';

    protected $fillable = [
                            'id',
                            'test',
                            'created_at',
                            'updated_at',
                            ];    
    //
}

