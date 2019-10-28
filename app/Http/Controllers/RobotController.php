<?php 

namespace App\Http\Controllers;

use App\Repositories\Criteria\IncludeCriteria;
use App\Repositories\RobotRepository;
use App\Repositories\UserRobotRepository;
use App\Transformers\RobotTransformer;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;


class RobotController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, RobotRepository $robotRepository)
    {
        $robots = $robotRepository->pushCriteria(new IncludeCriteria(['users']))
            ->get();

        return $this->respondWithCollection($robots, new RobotTransformer(), 'user');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return null;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        Request $request, 
        RobotRepository $robotRepository,
        UserRobotRepository $userRobotRepository
    ) {
        $this->validate($request, [
            'name' => 'required|string|unique:robots',
            'weight' => 'required|numeric|min:1',
            'power' => 'required|numeric|min:1',
            'speed' => 'required|numeric|min:1',
        ]);

        try {
            $robot = DB::transaction(
                function () use ($request, $robotRepository, $userRobotRepository) {
                    $robot = $robotRepository->create([
                        'name' => $request->input('name'),
                        'weight' => $request->input('weight'),
                        'power' => $request->input('power'),
                        'speed' => $request->input('speed'),
                    ]);
                    
                    $userRobotRepository->create([
                        'user_id' => $request->user()->getKey(),
                        'robot_id' => $robot->getKey(),
                    ]);

                    return $robot;
                }
            );

            return $this->respondWithItem($robot, new RobotTransformer(), [], 201);
        } catch (\Exception $exception) {
            Log::error('Robot creation encountered an unexpected error', [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
            ]);

            return $this->respondWithError("Robot creation encountered an Unexpected Error", 409);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(
        Request $request, 
        $id
    ) {
        return null;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return null;
    }
}
