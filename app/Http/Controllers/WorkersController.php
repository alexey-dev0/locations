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
        $worker = Worker::where('phone', $data['phone'])->first();


        $worker->latitude = $data['latitude'];
        $worker->longitude = $data['longitude'];
        $worker->last_update = Carbon::now('UTC');
        $worker->save();

        return response()->json([
            'success' => true,
            'request' => $data
        ]);
    }

    public function active() {
        $data = Request::all();
        $worker = Worker::whereId($data['id'])->first();
        $active = Carbon::now() - $worker->last_update < 30 * 1000;

        return response()->json([
            'success' => true,
            'active' => $active
        ]);
    }
}
