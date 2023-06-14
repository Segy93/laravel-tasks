<?php

use App\Http\Controllers\AjaxController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', [TaskController::class, 'taskList'])
    ->name('taskList')
;

Route::get('/create-task', [TaskController::class, 'taskCreate'])
    ->name('taskCreate')
;

Route::get('/edit-task/{task_id}', [TaskController::class, 'taskEdit'])
    ->name('taskEdit')
;

Route::post('/create-task-post', [TaskController::class, 'taskCreatePost'])
    ->name('taskCreatePost')
;

Route::post('/update-task', [TaskController::class, 'taskUpdate'])
    ->name('taskUpdate')
;

Route::post('/delete-quiz', [TaskController::class, 'taskDelete'])
    ->name('taskDelete')
;




/**
 * Ajax routes
 */
Route::post('ajax/data', [AjaxController::class, 'handleRequestRegular']);
Route::post('ajax/data_raw', [AjaxController::class, 'handleRequestRaw']);
