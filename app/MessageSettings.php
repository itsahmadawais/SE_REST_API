<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageSettings extends Model
{
    public $timestamps = false;
	protected $table = 'engine4_emessages_usersettings';
	protected $primaryKey = "usersetting_id";
}
