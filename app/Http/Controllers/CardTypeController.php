<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCardTypeRequest;
use App\Models\Column;
use App\Models\Company;
use App\Models\CardType;
use App\Models\Team;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use phpDocumentor\Reflection\DocBlock\Tags\Author;

class CardTypeController extends Controller
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
            CardType::getRecords($request->toArray())->addConstraints(function ($query) use($request){
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
     * @param StoreCardTypeRequest $request
     * @return JsonResponse
     */
    public function store(StoreCardTypeRequest $request) : JsonResponse
    {
        $cardType = CardType::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'تایپ کارد']), [
            'card_type' => $cardType
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param CardType $cardType
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show(CardType $cardType) : JsonResponse
    {
        if (!Company::isCompanyOwner(Company::findOrFail($cardType->company_ref_id),auth()->user()->user_id))
            throw new AuthorizationException('شما به این تایپ کارد دسترسی ندارید');
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'تایپ کارد']), [
            'card_type' => $cardType
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StoreCardTypeRequest $request
     * @param CardType $cardType
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(StoreCardTypeRequest $request, CardType $cardType) : JsonResponse
    {
        if (!Company::isCompanyOwner(Company::findOrFail($cardType->company_ref_id),auth()->user()->user_id))
            throw new AuthorizationException('شما به این تایپ کارد دسترسی ندارید');
        $cardType->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update',['resource'=>'تایپ کارد']), [
            'card_type' => $cardType
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $cardType
     * @return JsonResponse
     */
    public function destroy($cardType) : JsonResponse
    {
        $count = CardType::destroy(explode(',',$cardType));
        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$count]));
        return response()->json($response, $response['statusCode']);
    }

    public function getAvailableCardTypes(Request $request): JsonResponse
    {
        $request->validate([
           'team_ref_id' => ['required']
        ]);

        $team = Team::query()->findOrFail($request->get('team_ref_id'));
        $response = $this->getResponse(__('apiResponse.index',['resource' => 'تایپ کارد']),[
           CardType::query()->where('company_ref_id', $team->project->company->company_id)->get()
        ]);
        return response()->json($response,$response['statusCode']);
    }

    public function getCardTypeColumns(CardType $cardType, Team $team): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index',['resource'=>'برای کارد تایپ انتخابی فیلد']),[
           Column::getCardTypeColumns($cardType->card_type_id,$team->team_id)
        ]);
        return response()->json($response,$response['statusCode']);
    }
}
