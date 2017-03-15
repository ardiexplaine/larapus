<?php

namespace App\Http\Requests;

use App\Author;

class UpdateAuthorRequest extends StoreAuthorRequest
{
    public function rules()
    {
        $rules = parent::rules();
        //$rules['name'] = 'required|unique:authors,name,'. $id . $this->route('author');
        $rules['name'] = 'required|unique:authors,name,' . $this->route('author');

        return $rules;
    }
}