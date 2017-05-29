<?php
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'data', 'middleware' => 'auth', 'namespace' => 'Data'], function () {
    Route::any('customer',['uses'=>'ContractController@customer','as'=>'data-contract-customer']);
    Route::any('landlord',['uses'=>'ContractController@landlord','as'=>'data-contract-landlord']);
});
