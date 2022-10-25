<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClientCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next (\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param string|null $client
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next,?string $client = null)
    {
        $clientToken = $request->header('ClientToken');
        $currentClient = Client::query()->where('apiCode',$clientToken)->first();
        if (!is_null($client) && (empty($currentClient) || $currentClient->name !== $client)){
            $response = (new Controller())->getError('invalid client',[]);
            return \response()->json($response,$response['statusCode']);
        }
        $request->merge([
            'ClientName' => !empty($currentClient) ? $currentClient->name : 'NONE'
        ]);
        return $next($request);
    }
}
