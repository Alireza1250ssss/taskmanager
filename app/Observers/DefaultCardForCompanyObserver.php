<?php

namespace App\Observers;

use App\Models\CardType;
use App\Models\Company;

class DefaultCardForCompanyObserver
{
    public function created(Company $company)
    {
        $cardType = CardType::createDefaultCardTypeFor($company);
        $cardType->makeDefaultColumns();
    }
}
