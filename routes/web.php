<?php

use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Auth::routes();

// Home
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/user', 'HomeController@user');
Route::get('/terminaltest', function () {
    return view('terminal-test');
});
Route::get('terminalhosts', function () {

});

Route::group(['middleware'=>['auth', 'admin']], function() {
    //Library
    Route::resource('/editor', 'ArticleEditorController');
    Route::post('/deleteArticle', 'ArticleEditorController@deleteArticle');
    Route::post('/addArticle', 'ArticleEditorController@addArticle');
});


Route::group(['middleware'=>['auth', 'instructor']], function() {
    // Class
    Route::resource('/classes', 'ClassController');
    Route::resource('/assignments', 'AssignmentController');
    Route::post('/retrieveassignments', 'AssignmentController@retrieveAssignments');
});



Route::group(['middleware'=>['auth', 'student']], function() {
    // Simulation
    Route::post('/updateday', 'SimulationController@updateDay');
    Route::get('/sim', function () {
        return DB::table('simulation')->get();
    });
    Route::post( '/resetDay', 'SimulationController@resetDay' );
    Route::post('/getemailnotifications', 'SimulationController@getEmailNotifications');
    //Route::post('/getgallerynotifications', 'SimulationController@getGalleryNotifications');
    //Route::post('/visitedgallery', 'SimulationController@visitedGallery');


// Tasks
    Route::resource('/tasks', 'TasksController');
    Route::post('/tasks/complete', 'TasksController@complete');

// Email
    Route::resource('/email', 'EmailController');
    Route::post('/returnemails', 'EmailController@emailData');
    Route::post('/readEmail', 'EmailController@readEmail');

// Video Call
    Route::resource('/videocall', 'VideoCallController');
    Route::post('/saveFile', 'VideoCallController@saveFile');
    Route::post('/clickedQuestion', 'VideoCallController@disableQuestion');

// Library
    Route::resource('/library', 'LibraryController');
// Route::post('/updateArticle', 'ArticleEditorController@update');

// Chat
    Route::resource('/chat', 'ChatController');
    Route::get('/chatbot', function () {
        return view('chatbot');
    });

// Gallery
    //Route::get( '/getartifacts', 'GalleryController@getArtifacts' );
    //Route::resource('/gallery', 'GalleryController');

// Group
    Route::resource('/group', 'GroupController');
    Route::post('/addToGroup', 'GroupController@addToGroup');
    Route::post('removeFromGroup', 'GroupController@removeFromGroup');

// Terminal
    Route::get('/terminal','TerminalController@index');

// Dash
    Route::get('/dashboard', 'DashController@loadDash');
});


