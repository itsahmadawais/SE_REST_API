<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{

	public $timestamps = false;

	protected $table = 'engine4_users';
	protected $primaryKey = 'user_id';
}
