<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Requests\UserAssignViewRequest;
use App\Models\Column;
use App\Models\Company;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $companies = Company::getRecords($request->toArray())->addConstraints(function ($query) {
            $query->with(['projects.teams','cardTypes.columns']);
            $query->whereIn('company_id',auth()->user()->companiesJoined->pluck('company_id')->toArray());
        })->get();
        $companies = $companies->filter(function ($item,$key){
            return !empty($item->company_id);
        });
        Column::prepareColumns($companies);

        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'کمپانی']), [
            $companies->values()
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCompanyRequest $request
     * @return JsonResponse
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = Company::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store', ['resource' => 'کمپانی']), [
            'company' => $company
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Company $company
     * @return JsonResponse
     */
    public function show(Company $company): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show', ['resource' => 'کمپانی']), [
            'company' => $company->load('projects.teams')
        ]);
        return response()->json($response, $response['statusCode']);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCompanyRequest $request
     * @param Company $company
     * @return JsonResponse
     */
    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $company->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update', ['resource' => 'کمپانی']), [
            'company' => $company->load('projects')
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $company
     * @return JsonResponse
     */
    public function destroy($company): JsonResponse
    {
        $count = Company::destroy(explode(',', $company));
        $response = $this->getResponse(__('apiResponse.destroy', ['items' => $count]));
        return response()->json($response, $response['statusCode']);
    }

}
