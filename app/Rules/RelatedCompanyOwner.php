<?php

namespace App\Rules;

use App\Models\Company;
use Illuminate\Contracts\Validation\Rule;

class RelatedCompanyOwner implements Rule
{
    public string $classType;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($classType)
    {
        $this->classType = $classType;

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $relatedModel = $this->classType::findOrFail($value);
        if (!Company::isCompanyOwner(Company::findOrFail($relatedModel->company_ref_id),auth()->user()->user_id))
            return false;
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('apiResponse.not-company-owner');
    }
}
