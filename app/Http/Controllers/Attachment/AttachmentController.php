<?php

namespace App\Http\Controllers\Attachment;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttachmentController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param Attachment $attachment
     * @return BinaryFileResponse|JsonResponse
     */
    public function show(Attachment $attachment)
    {
        try {
            File::get(storage_path("app/" . $attachment->path));
            return response()->file(storage_path("app/" . $attachment->path));
        } catch (FileNotFoundException $e) {
            $response = $this->getNotFound(__('apiResponse.file-missing'));
            return response()->json($response,$response['statusCode']);
        }
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request) : JsonResponse
    {
        $request->validate([
            'uuids' => 'required|array' ,
            'uuids.*' => 'uuid'
        ]);
        $attachments = Attachment::query()->whereIn('id',$request->get('uuids',[]))->get();
        $attachments->each(fn ($item) => $item->delete());

        $response = $this->getResponse(__('apiResponse.destroy',['items'=>$attachments->count()]));
        return response()->json($response, $response['statusCode']);
    }
}
