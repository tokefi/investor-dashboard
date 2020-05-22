<?php

/** APIs */
Route::group(['prefix' => 'api', 'middleware' => ['throttle:10']], function () {
	Route::post('/register', 'Api\UserController@register');
});