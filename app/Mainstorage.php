<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mainstorage extends Model
{

	public $timestamps = false;

	protected $table = 'engine4_storage_files';
	const CREATED_AT="creation_date";
	protected $primaryKey = 'file_id';
  
}
