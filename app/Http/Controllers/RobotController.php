<?php

namespace App\Http\Controllers;

use App\Repositories\Criteria\IncludeCriteria;
use App\Repositories\Criteria\GenericCriteria;
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
        try {
            $robots = $robotRepository->pushCriteria(new IncludeCriteria(['users']))->get();
        } catch (\Exception $exception) {
            Log::error('Getting Robots encountered an unexpected error', [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
            ]);

            return $this->respondWithError("Getting Robots encountered an Unexpected Error", 409);
        }

        return $this->respondWithCollection($robots, new RobotTransformer(), 'user');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, RobotRepository $robotRepository)
    {
        try {
            $robot = $robotRepository->pushCriteria(new GenericCriteria(['id' => $id]))->first();

            if (!$robot) {
                return $this->respondWithError('Robot not found', 404);
            }

            return $this->respondWithItem($robot, new RobotTransformer(), ['user']);
        } catch (\Exception $exception) {
            Log::error('Showing Robot encountered an unexpected error', [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
            ]);

            return $this->respondWithError("Showing Robot encountered an Unexpected Error", 409);
        }
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
        $id,
        RobotRepository $robotRepository
    ) {
        $this->validate($request, [
            'weight' => 'nullable|numeric|min:1',
            'power' => 'nullable|numeric|min:1',
            'speed' => 'nullable|numeric|min:1',
        ]);

        try {
            $robot = $robotRepository->findByIdAndUserId($id, $request->user()->getKey());
            if (!$robot) {
                return $this->respondWithError('Robot not found', 404);
            }

            $dataToUpdate = array_filter([
                'weight' => $request->input('weight'),
                'power' => $request->input('power'),
                'speed' => $request->input('speed'),
            ]);

            if ($dataToUpdate) {
                $robot = $robotRepository->update($dataToUpdate, $id);
            }
        } catch (\Exception $exception) {
            Log::error('Robot update encountered an unexpected error', [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
            ]);

            return $this->respondWithError("Robot update encountered an Unexpected Error", 409);
        }

        return $this->respondWithItem($robot, new RobotTransformer(), ['user']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, RobotRepository $robotRepository, UserRobotRepository $userRobotRepository)
    {
        try {
            $robot = $robotRepository->findByIdAndUserId($id, Auth::user()->getKey());
            if (!$robot) {
                return $this->respondWithError('Robot not found', 404);
            }

            DB::transaction(function () use ($robotRepository, $id) {
                $robotRepository->delete($id);
            });

            return $this->respondWithNoContent();
        } catch (\Exception $exception) {
            Log::error('Robot deletion encountered an unexpected error', [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
            ]);

            return $this->respondWithError("Robot deletion encountered an Unexpected Error", 409);
        }
    }
}