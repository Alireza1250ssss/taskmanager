<?php

namespace App\Http\Controllers;

use App\Models\Preference;
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
        $preference = auth()->user()->preferences()->create([
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
        $response = $this->getResponse(__('apiResponse.show',['resource'=>'شخصی سازی']), [
            'preference' => auth()->user()->preferences
        ]);
        return response()->json($response, $response['statusCode']);
    }

}
