<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.index', ['resource' => 'کاربر']), [
            User::getRecords($request->toArray())->addConstraints(function ($query) use($request){
                if ($request->filled('access_entity'))
                    $query->whereHas('entities',function (Builder $builder) use($request){
                        $key = ResolvePermissionController::$models[$request->get('access_entity')]['class'];
                        $builder = $builder->where('key',$key)
                            ->where('action',$request->get('access_entity_action'));
                        if ($request->get('access_entity_action') !== 'create')
                            $builder->where('model_id',$request->get('access_entity_id'));
                    });
            })->get()
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        $response = $this->getResponse(__('apiResponse.store', ['resource' => 'کاربر']), [
            'user' => $user
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $response = $this->getResponse(__('apiResponse.show', ['resource' => 'کاربر']), [
            'user' => $user
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());
        $response = $this->getResponse(__('apiResponse.update', ['resource' => 'کاربر']), [
            'user' => $user
        ]);

        return response()->json($response, $response['statusCode']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $user
     * @return JsonResponse
     */
    public function destroy($user): JsonResponse
    {
        $count = User::destroy(explode(',', $user));
        $response = $this->getResponse(__('apiResponse.destroy', ['items' => $count]));
        return response()->json($response, $response['statusCode']);
    }

}
