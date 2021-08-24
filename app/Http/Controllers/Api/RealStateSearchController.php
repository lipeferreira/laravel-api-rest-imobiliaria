<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RealState;
use App\Repository\RealStateRepository;
use App\Api\ApiMessages;

class RealStateSearchController extends Controller
{
    private $realState;

    public function __construct(RealState $realState)
    {
        $this->realState = $realState;
    }

    public function index(Request $request)
    {
        $repository = new RealStateRepository($this->realState);

        if($request->has('coditions')) {
		    $repository->selectCoditions($request->get('coditions'));
	    }

	    if($request->has('fields')) {
		    $repository->selectFilter($request->get('fields'));
	    }

        $repository->setLocation($request->all(['state', 'city']));

        return response()->json(['data' => $repository->getResult()->paginate(10)], 200);
    }

    public function show($id)
    {
        try {
            $rs = $this->realState->with('adress')->with('photos')->findOrFail($id);
            return response()->json(['data' => $rs], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json(['error' => $message->getMessage()], 401);
        }
    }
}
