<?php

namespace App\Http\Controllers;

use App\Models\Checksheet;
use App\Models\Department;
use App\Models\Competency;
use Illuminate\Http\Request;

class ChecksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Checksheet';
        $checksheets  = Checksheet::with('department')->paginate(10);
        $departments  = Department::all();
        $competencies= Competency::all();

        return view('website.checksheet.index', compact(
            'checksheets', 'departments', 'title', 'competencies'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = Department::all();

        return view('checksheet.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'competency_id' => 'required|exists:competency,id',
            'name'          => 'required|string|max:191',
            'department_id' => 'required|exists:departments,id',
            'position'      => 'required|string'
        ]);
    
        Checksheet::create($request->only([
            'competency_id','name','position','department_id'
        ]));
    
        return response()->json(['message'=>'Checksheet added successfully!'],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Checksheet  $checksheet
     * @return \Illuminate\Http\Response
     */
    public function show(Checksheet $checksheet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Checksheet  $checksheet
     * @return \Illuminate\Http\Response
     */
    public function edit(Checksheet $checksheet)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Checksheet  $checksheet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Checksheet $checksheet)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Checksheet  $checksheet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Checksheet $checksheet)
    {
        $checksheet->delete();

        return response()->json(['message' => 'Checksheet deleted successfully!']);
    }
}
