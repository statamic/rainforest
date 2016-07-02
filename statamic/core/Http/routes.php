<?php

use Statamic\API\OAuth;
use Statamic\API\Config;

start_measure('routing', 'Running routes');

/**
 * The Statamic Installer
 */
Route::controller('installer', 'InstallerController');

/**
 * Control Panel
 */
if (CP_ROUTE !== false) {
    require __DIR__ . '/routes-cp.php';
}

/**
 * Glide
 * On-the-fly URL-based image transforms.
 */
Route::group(['prefix' => Config::get('assets.image_manipulation_route')], function () {
    get('/id/{id}/{filename?}', 'GlideController@generateByAsset');
    get('/{path?}', 'GlideController@generateByPath')->where('path', '.*');
});

/**
 * OAuth Social Authentication
 */
if (OAuth::enabled()) {
    Route::group(['prefix' => OAuth::route()], function () {
        Route::get('{provider}', ['uses' => 'Auth\OAuthController@redirectToProvider', 'as' => 'oauth']);
        Route::get('{provider}/callback', ['uses' => 'Auth\OAuthController@handleProviderCallback', 'as' => 'oauth.callback']);
    });
}

/**
 * URL Event Trigger
 * Defaults to /!/foo/bar, but the ! can be changed.
 */
Route::any(EVENT_ROUTE . '/{namespace?}/{event?}/{params?}', 'StatamicController@eventTrigger')->where('params', '.*');

/**
 * Front-end
 * All front-end website requests go through a single controller method.
 */
Route::any('/{segments?}', 'StatamicController@index')->where('segments', '.*')->name('site');

stop_measure('routing');
