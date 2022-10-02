<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonalRequest;
use App\Models\Company;
use App\Models\Personal;
use App\Models\Team;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use phpDocumentor\Reflection\DocBlock\Tags\Author;

class PersonalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $request->validate([
           'company_id' => 'required'
        ]);
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'تایپ کارد']),[
            Personal::getRecords($request->toArray())->addConstraints(function ($query) use($request){
                $companyId = $request->get('company_id');
                $company = Company::findOrFail($companyId);
                if (!Company::isCompanyOwner($company,auth()->user()->user_id))
                    throw ValidationException::withMessages(['company_id' => 'شما سازنده این کمپانی نمی باشید']);
                $query->where('company_ref_id',$companyId);
            })->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePersonalRequest $request
     * @return JsonResponse
     */
    public function store(StorePersonalRequest $request) : JsonResponse
    {
        $personal = Personal::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'تایپ کارد']), [
            'personal' => $personal
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Personal $personal
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show(Personal $personal) : JsonResponse
    {
        if (!Company::isCompanyOwner(Company::findOrFail($personal->company_ref_id),auth()->user()->user_id))
            throw new AuthorizationException('شما به این تایپ کارد دسترسی ندارید');
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'تایپ کارد']), [
            'personal' => $personal
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StorePersonalRequest $request
     * @param Personal $personal
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(StorePersonalRequest $request, Personal $personal) : JsonResponse
    {
        if (!Company::isCompanyOwner(Company::findOrFail($personal->company_ref_id),auth()->user()->user_id))
            throw new AuthorizationException('شما به این تایپ کارد دسترسی ندارید');
        $personal->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update',['resource'=>'تایپ کارد']), [
            'personal' => $personal
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $personal
     * @return JsonResponse
     */
    public function destroy($personal) : JsonResponse
    {
        $count = Personal::destroy(explode(',',$personal));
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response, $response['statusCode']);
    }

    public function getAvailablePersonals(Request $request): JsonResponse
    {
        $request->validate([
           'team_ref_id' => ['required']
        ]);

        $team = Team::query()->findOrFail($request->get('team_ref_id'));
        $response = $this->getResponse(__('apiResponse.index',['resource' => 'تایپ کارد']),[
           Personal::query()->with('columns')->where([
               'level_type' => 'team',
               'level_id' => $team->team_id
           ])->orWhere(function (Builder $builder) use($team){
               $builder->where([
                   'level_type' => 'project',
                   'level_id' => $team->project->project_id
               ]);
           })->orWhere(function (Builder $builder) use($team){
               $builder->where([
                   'level_type' => 'company',
                   'level_id' => $team->project->company->company_id
               ]);
           })->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }
}
