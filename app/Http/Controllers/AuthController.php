<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Transformers\UserTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class AuthController extends ApiController
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function register(Request $request, UserRepository $userRepository)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        try {
            $user = $userRepository->create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => app('hash')->make($request->input('password')),
            ]);
            
            return $this->respondWithItem($user, new UserTransformer(), [], 201);
        } catch (\Exception $exception) {
            Log::error('Register encountered an unexpected error', [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
            ]);

            return $this->respondWithError("Registering User Encountered an Unexpected Error", 409);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return $this->respondWithArray(['message' => 'Unauthorized'], [], 401);
        }

        return $this->respondWithToken($token);
    }
}