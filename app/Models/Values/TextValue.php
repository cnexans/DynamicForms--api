<?php

namespace App\Models\Values;

use Illuminate\Database\Eloquent\Model;

class TextValue extends Model
{
	use ValueTrait;

	protected $table = 'text_values';

	public $timestamps = false;

	protected $fillable = ['field_descriptor_id', 'form_answer_id', 'value'];

	public function __construct()
	{
		parent::__construct();

		$this->value_type = 'text';
	}  
}
