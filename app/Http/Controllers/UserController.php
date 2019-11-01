<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\Transformers\UserTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, UserRepository $userRepository)
    {
        $this->validate($request, [
            'limit' => 'nullable|integer|min:1|max:'.env('PAGINATION_LIMIT'),
            'page' => 'nullable|integer|min:1',
        ]);

        $limit = env('PAGINATION_LIMIT');
        if ($request->exists('limit')) {
            $limit = $request->input('limit');
        }

        $page = 1;
        if ($request->exists('page')) {
            $page = $request->input('page');
        }

        try {
            $users = $userRepository->paginate($limit, $page);
        } catch (Exception $exception) {
            Log::error('Getting users encountered an unexpected error', [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
            ]);

            return $this->respondWithError("Getting users encountered an Unexpected Error", 409);
        }

        return $this->respondWithCollection($users, new UserTransformer());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @param  \App\Repositories\UserRepository $userRepository
     *
     * @return \Illuminate\Http\Response
     */
    public function update(
        Request $request,
        $id,
        UserRepository $userRepository
    ) {
        $this->validate($request, [
            'name' => 'nullable|string',
        ]);

        $user = $request->user();
        $userId = $user->getKey();
        if ($userId != $id) {
            return $this->respondWithError('User does not match authenticated user', 422);
        }

        try {
            $dataToUpdate = array_filter(['name' => $request->input('name')]);

            if ($dataToUpdate) {
                $user = $userRepository->update($dataToUpdate, $userId);
            }
        } catch (Exception $exception) {
            Log::error('User update encountered an unexpected error', [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
            ]);

            return $this->respondWithError("User update encountered an Unexpected Error", 409);
        }

        return $this->respondWithItem($user, new UserTransformer());
    }
}
