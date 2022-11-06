<?php

namespace App\Http\Controllers;

use App\Models\Preference;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PreferenceController extends Controller
{


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request) : JsonResponse
    {
        $request->validate([
           'column_preference' => 'required|array'
        ]);
        $preference = auth()->user()->preferences()->updateOrCreate([],[
            'column_preference' => $request->get('column_preference',[])
        ]);
        $response = $this->getResponse(__('apiResponse.store',['resource'=>'شخصی سازی']), [
            'preference' => $preference
        ]);
        return response()->json($response, $response['statusCode']);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $preferences = auth()->user()->preferences->isNotEmpty() ? auth()->user()->preferences :
            auth()->user()->preferences()->create([
               'column_preference' => array_fill_keys((new Task())->getFillable(),true)
            ]);
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'شخصی سازی']), [
            'preference' => $preferences
        ]);
        return response()->json($response, $response['statusCode']);
    }

}
