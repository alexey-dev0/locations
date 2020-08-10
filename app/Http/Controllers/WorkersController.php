<?php


namespace App\Http\Controllers;

use App\Worker;
use Carbon\Carbon;
use Request;

class WorkersController extends Controller
{
    public function index() {
        return Worker::all()->jsonSerialize();
    }

    public function store() {
        $data = Request::all();
        $data['last_update'] = Carbon::now();
        $data['created_at'] = Carbon::now();
        $data['id'] = Worker::create($data)->id;

        return response()->json([
            'success' => true,
            'request' => $data
        ]);
    }

    public function update() {
        $data = Request::all();
        $worker = Worker::wherePhone($data['phone']);


        $worker->latitude = $data['latitude'];
        $worker->longitude = $data['longitude'];
        $worker->last_update = Carbon::now();
        $worker->save();

        return response()->json([
            'success' => true,
            'request' => $data
        ]);
    }
}
