<?php

namespace App\Observers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Auth\Access\AuthorizationException;

class CompanyObserver
{
    public function retrieved($company)
    {
//        dd($company);
//       throw new AuthorizationException();
    }

    /**
     * Handle the Company "updated" event.
     *
     * @param \App\Models\Company $company
     * @return void
     */
    public function updating($company)
    {
//        dd($company);
    }

    /**
     * Handle the Company "deleted" event.
     *
     * @param \App\Models\Company $company
     * @return void
     */
    public function deleting(Company $company)
    {
//        dd($company);
    }

    /**
     * Handle the Company "restored" event.
     *
     * @param \App\Models\Company $company
     * @return void
     */
    public function restored(Company $company)
    {
        //
    }

    /**
     * Handle the Company "force deleted" event.
     *
     * @param \App\Models\Company $company
     * @return void
     */
    public function forceDeleted(Company $company)
    {
        //
    }
}
