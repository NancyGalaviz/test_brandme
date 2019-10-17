<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'token_access';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'user_token', 'data_access_expires_at'];

}
