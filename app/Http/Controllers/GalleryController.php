<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
<<<<<<< HEAD
use Illuminate\Support\Facades\DB;
=======
>>>>>>> master

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
<<<<<<< HEAD
        //$base = DB::table('artifacts')->get();
        return view('gallery');

    }

    public function getArtifacts()
    {
        $baseArtifacts = DB::table('artifacts')
            //->where('created_by', 0)
            ->get();
        //$userArtifacts = DB::table('artifacts')
          //  ->where('created_by', $id)
           // ->get();
//        foreach ($baseArtifacts as $artifact) {
//            foreach ($userArtifacts as $editedArtifact) {
//                if($artifact->id == $editedArtifact->edit_id) {
//                    $artifact = $editedArtifact;
//                    break;
//                }
//            }
//        }
        return $baseArtifacts;
=======
        return view('gallery');
>>>>>>> master
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
        //
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
