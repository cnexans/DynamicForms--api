<?php

namespace App\Models\Values;

use Illuminate\Database\Eloquent\Model;

class BlobValue extends Model
{
	use ValueTrait;

	protected $table = 'blob_values';

	public $timestamps = false;

	protected $fillable = ['field_descriptor_id', 'form_answer_id', 'value', 'mine_type'];

	public function __construct()
	{
		parent::__construct();

		$this->value_type = 'blob';
	}  
}
