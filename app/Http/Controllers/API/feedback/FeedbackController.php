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
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
            $data = $request->all();
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('attachments', 'public');
                $data['attachment'] = $path;
            }
            $data['user_id'] = auth()->id();
            $feedback = Feedback::create($data);
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
        try {
            $feedback = Feedback::findOrFail($id);
            return response()->json(new FeedbackResource($feedback), Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Feedback not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve feedback', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FeedbackRequest $request, $id)
    {
        try {
            $feedback = Feedback::findOrFail($id);
            $user = $feedback->user;
            $data = $request->all();
            if ($request->hasFile('attachment')) {
                $feedback->attachment ? Storage::delete($feedback->attachment) : null;
                $file = $request->file('attachment');
                $path = $file->store('attachments', 'public');
                $data['attachment'] = $path;
            } else {
                $path = $feedback->attachment;
               $data['attachment'] = $path;
            }
            $feedback->update($data);
            $user->notify(new FeedbackUpdated());
            return response()->json(new FeedbackResource($feedback), Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Feedback not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update feedback', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            Feedback::findOrFail($id)->delete();
            return response()->json(['message' => 'Feedback deleted successfully'], Response::HTTP_OK);
        } catch (\Throwable $e) {
            return response()->json(['errors' => $e instanceof ModelNotFoundException ? 'Feedback not found' : 'Failed to delete feedback'], $e instanceof ModelNotFoundException ? Response::HTTP_NOT_FOUND : Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }    
    public function userFeedback(Request $request)
    {
        $feedbacks = $request->user()->feedbacks()->latest()->get();
        return response()->json(FeedbackResource::collection($feedbacks), 200);
    }
}
