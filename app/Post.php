<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

	public $timestamps = false;

	protected $table = 'engine4_activity_actions';
	const CREATED_AT="date";
	protected $primaryKey = 'action_id';
	
  
}
