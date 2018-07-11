<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\TaskToggle;
use Illuminate\Support\Facades\Auth;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = DB::table('tasks')
            ->get();


        foreach ($tasks as $task) {
            $studentTask = DB::table('student_tasks')->where([
                ['task_id', $task->task_id],
                ['student_id', Auth::id()]
            ])->first();

            if ($studentTask) {
                $task->complete = $studentTask->complete;
            } else {
                $task->complete = 0;
            }
        }

        return $tasks;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    public function complete(Request $request) {
        $task = DB::table('student_tasks')
            ->where([
                ['task_id', $request->id],
                ['student_id', Auth::id()]
            ])
            ->first();


        if ($task) {
            $complete = !$task->complete;

            DB::table('student_tasks')
                ->where([
                    ['task_id', $request->id],
                    ['student_id', Auth::id()]
                ])
                ->update(['complete' => $complete, 'updated_at' => DB::raw('NOW()')]);
        } else {
            DB::table('student_tasks')
                ->insert([
                    'task_id' => $request->id,
                    'complete' => 1,
                    'student_id' => Auth::id(),
                    'created_at' => DB::raw('NOW()')
                ]);

        }

        return ('Good');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
