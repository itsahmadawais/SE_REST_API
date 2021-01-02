<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{

	public $timestamps = false;

	protected $table = 'engine4_album_photos';
	const CREATED_AT="creation_date";
		protected $primaryKey = 'photo_id';
	
  
}
