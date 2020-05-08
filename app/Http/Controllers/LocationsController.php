<?php

namespace App\Http\Controllers;

use App\Location;
use Auth;
use Carbon\Carbon;
use Request;

class LocationsController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $locations = Location::where('user_id', Auth::user()->id)
                ->orderBy('created_at', 'desc')
                ->get();
            return view('locations', compact('locations'));
        } else {
            return redirect('/login');
        }
    }

    protected function validator(array $data) {
        $messages = [
            'required' => 'Поле :attribute должно быть заполнено.',
            'regex' => 'Неверный формат ввода поля :attribute.',
            'string' => 'Поле :attribute должно быть строкой.',
            'min' => 'Поле :attribute содержит меньше :min символов.',
            'max' => 'Поле :attribute содержит больше :max символов.'
        ];

        $attributes = [
            'name' => 'имя',
            'latitude' => 'широта',
            'longitude' => 'долгота'
        ];

        $validator = \Validator::make($data, [
            'name' => 'required|string|min:3|max:40',
            'latitude' => ['required','regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'longitude' => ['required','regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/']
        ], $messages);
        $validator->setAttributeNames($attributes);

        return $validator;
    }

    public function store()
    {
        if (!Auth::check()) abort(403, 'Access denied');
        $data = Request::all();

        $validator = $this->validator($data);
        if ($validator->fails())
        {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()->all()
            ]);
        }

        $data['user_id'] = Auth::id();
        $data['created_at'] = Carbon::now();
        $data['id'] = Location::create($data)->id;

        return response()->json([
            'success' => true,
            'request' => $data
        ]);
    }

    public function update()
    {
        if (!Auth::check()) abort(403, 'Access denied');

        $data = Request::all();
        $location = Location::findOrFail($data['id']);

        if (Auth::id() != $location['user_id']) abort(403, 'Access denied');

        $location->name = $data['name'];
        $location->latitude = $data['latitude'];
        $location->longitude = $data['longitude'];
        $location->save();

        return response()->json([
            'success' => true,
            'request' => $data
        ]);
    }

    public function delete()
    {
        if (!Auth::check()) abort(403, 'Access denied');

        $data = Request::all();
        $location = Location::findOrFail($data['id']);

        if (Auth::id() != $location['user_id']) abort(403, 'Access denied');

        $location->delete();

        return response()->json([
            'success' => true,
            'request' => $data
        ]);
    }
}
