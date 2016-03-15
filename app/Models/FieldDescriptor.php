<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldDescriptor extends Model
{
    protected $table = 'field_descriptors';


    public function getOwnerForm()
    {
    	return Form::find( $this->form_id );
    }
}