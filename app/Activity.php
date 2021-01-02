<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Post;
class Activity extends Model
{

	public $timestamps = false;

	protected $table = 'engine4_activity_stream';
	
	public function mypost(){
	    return $this->belongsTo(Post::class,'target_id','target_id');
	}
	
	
  
}
