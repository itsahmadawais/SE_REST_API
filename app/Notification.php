<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = false;
	protected $table = 'engine4_activity_notificationsettings';
	protected $primaryKey = false;
}
