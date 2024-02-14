<?php

namespace App\Http\Controllers\API\feedback;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Http\Requests\API\feedback\FeedbackRequest;
use App\Http\Resources\FeedbackResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use App\Notifications\FeedbackUpdated;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feedbacks = Feedback::latest()->get();
        return response()->json(FeedbackResource::collection($feedbacks), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FeedbackRequest $request)
    {
        try {
            $path = '';
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('attachments', 'public');
            }


            $request->merge([
                'user_id' => auth()->id(),
                'attachment' => $path
            ]);
            $feedback = Feedback::create(
                $request->all()
            );
            return response()->json(new FeedbackResource($feedback), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create feedback', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $feedback = Feedback::findOrFail($id);
        return response()->json(new FeedbackResource($feedback), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FeedbackRequest $request, $id)
    {
        try {
            $feedback = Feedback::findOrFail($id);
            $user = $feedback->user;
            if ($request->hasFile('attachment')) {
                Storage::delete($feedback->attachment);
                $file = $request->file('attachment');
                $path = $file->store('attachments', 'public');
            } else {
                $path = $feedback->attachment;
            }
            $request->merge([
                'attachment' => $path
            ]);
            $feedback->update($request->all());
            $user->notify(new FeedbackUpdated());
            return response()->json(new FeedbackResource($feedback), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update feedback', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    { try {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted successfully'], Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to delete feedback', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    }
    public function userFeedback(Request $request)
    {
        $feedbacks = $request->user()->feedbacks()->latest()->get();
        return response()->json(FeedbackResource::collection($feedbacks), 200);
    }

    public function uploadAttachment(Request $request)
    {
        try {
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('attachments', 'public');

                // Return the path of the uploaded file
                return response()->json(['path' => $path], 200);
            } else {
                return response()->json(['message' => 'No file uploaded'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to upload attachment', 'error' => $e->getMessage()], 500);
        }
    }
}
