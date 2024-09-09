<?php

namespace App\Rules;

use App\Models\ProductCategory;
use Illuminate\Contracts\Validation\Rule;

class ProductCategoryIdRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $explode = explode('_', $value);
        if ($explode[0] == 'new' && $explode[1] !== '') {
            return true;
        } else {
            return ProductCategory::find($value);
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Data yang dipilih tidak valid.';
    }
}
