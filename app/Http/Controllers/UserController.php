<?php 

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use  App\Models\User;

class UserController extends Controller
{
     /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get all User.
     *
     * @return Response
     */
    public function get()
    {
         return response()->json(['users' =>  User::all()], 200);
    }
}