<?php

namespace App\Http\Controllers;

use App\Libraries\Battles\BattleHelper;
use App\Repositories\Criteria\GenericCriteria;
use App\Repositories\Criteria\IncludeCriteria;
use App\Repositories\Criteria\OrderCriteria;
use App\Repositories\BattleRepository;
use App\Repositories\ChallengerRepository;
use App\Repositories\RobotRepository;
use App\Transformers\BattleResultTransformer;
use App\Transformers\BattleTransformer;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class BattleController extends ApiController
{
    /**
     * Creates a Battle and Challengers between Robots
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Repositories\RobotRepository $robotRepository
     * @param  \App\Repositories\BattleRepository $battleRepository
     * @param  \App\Repositories\ChallengerRepository $challengerRepository
     * @param  \App\Libraries\Battles\BattleHelper $battleHelper
     *
     * @return \Illuminate\Http\Response
     */
    public function fight(
        Request $request,
        RobotRepository $robotRepository,
        BattleRepository $battleRepository,
        ChallengerRepository $challengerRepository,
        BattleHelper $battleHelper
    ) {
        $this->validate($request, [
            'location' => 'required|string',
            'robot_id' => 'required|integer|exists:robots,id|different:opponent_robot_id',
            'opponent_robot_id' => 'required|integer|exists:robots,id|different:robot_id',
        ]);

        try {
            $userId = $request->user()->getKey();
            $robot = $robotRepository->findByIdAndUserId($request->input('robot_id'), $userId);
            if (!$robot) {
                return $this->respondWithError('Robot selected does not belong to you', 422);
            }

            // Set Date Range for counting the challenges the robots did
            $carbonDate = new Carbon();
            $startDate = $carbonDate->startOfDay()->format('Y-m-d H:i:s');;
            $endDate = $carbonDate->endOfDay()->format('Y-m-d H:i:s');

            // Validate the Robot's challenge count
            $maxRobotChallenges = env('MAX_ROBOT_CHALLENGES');
            $robotChallengedCount = $challengerRepository->countChallenges(
                $request->input('robot_id'),
                true,
                $startDate,
                $endDate
            );
            if ($robotChallengedCount >= $maxRobotChallenges) {
                return $this->respondWithError('Robot has fought '.$maxRobotChallenges.' battles already. Please try tomorrow!');
            }

            // Validate the Opponent Robot's challenge count
            $maxOpponentRobotChallenges = env('MAX_OPPONENT_ROBOT_CHALLENGES');
            $opponentRobotChallengedCount = $challengerRepository->countChallenges(
                $request->input('opponent_robot_id'),
                false,
                $startDate,
                $endDate
            );
            if ($opponentRobotChallengedCount >= $maxOpponentRobotChallenges) {
                return $this->respondWithError('Opponent Robot has already been challenged for today!');
            }

            // Query the Robots selected to fight against
            $robotIds = [$request->input('robot_id'), $request->input('opponent_robot_id')];
            $robots = $robotRepository->pushCriteria(new IncludeCriteria(['users']))
                ->pushCriteria(new GenericCriteria(['id' => $robotIds]))
                ->get();

            $winningRobot = $battleHelper->holdTournament(clone($robots));

            // Create the Battle and Challenges Records
            $battle = $battleRepository->create(['location' => $request->input('location')]);
            $battleId = $battle->getKey();

            $challengers = [];
            foreach ($robots as $robot) {
                $isInitiator = ($robot->getKey() == $request->input('robot_id'));
                $isVictorious = ($robot->getKey() == $winningRobot->getKey());
                $challengers[] = [
                    'battle_id' => $battleId,
                    'user_id' => $robot->user()->getKey(),
                    'robot_id' => $robot->getKey(),
                    'is_initiator' => $isInitiator,
                    'is_victorious' => $isVictorious,
                    'created_at' => Carbon::now(),
                ];
            }

            $challengerRepository->insert($challengers);

            return $this->respondWithItem($battle, new BattleTransformer());
        } catch (Exception $exception) {
            Log::error('Robot Fight encountered an unexpected error', [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
            ]);

            return $this->respondWithError("Robot Fight encountered an Unexpected Error", 409);
        }
    }

    /**
     * Display a listing of the Battle Results.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Repositories\BattleRepository $battleRepository
     *
     * @return \Illuminate\Http\Response
     */
    public function results(Request $request, BattleRepository $battleRepository)
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
            $battles = $battleRepository->pushCriteria(new IncludeCriteria(['challengers.robot']))
                ->pushCriteria(new OrderCriteria('id', 'desc'))
                ->paginate($limit, $page);

            return $this->respondWithCollection($battles, new BattleResultTransformer());
        } catch (Exception $exception) {
            Log::error('Battle Results encountered an unexpected error', [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
            ]);

            return $this->respondWithError("Battle Results encountered an Unexpected Error", 409);
        }
    }

    /**
     * Display a listing of the Robot Leaderboard.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Repositories\BattleRepository $battleRepository
     * @param  \App\Repositories\ChallengerRepository $challengerRepository
     *
     * @return \Illuminate\Http\Response
     */
    public function leaderboard(Request $request, BattleRepository $battleRepository, ChallengerRepository $challengerRepository)
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
            $leaderboard = $challengerRepository->getLeaderboard($page, $limit);

            return $this->respondWithArray(['data' => $leaderboard]);
        } catch (Exception $exception) {
            Log::error('Battle Results encountered an unexpected error', [
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
            ]);

            return $this->respondWithError("Battle Results encountered an Unexpected Error", 409);
        }
    }
}
