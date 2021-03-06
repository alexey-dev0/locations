<?php
Auth::routes();

Route::view('/', 'about');
Route::view('/about', 'about')->name('about');

Route::get('locations', 'LocationsController@index')->name('locations');
Route::post('locations', 'LocationsController@store');
Route::post('locations/delete/{id}', 'LocationsController@delete');
Route::post('locations/{id}', 'LocationsController@update');

