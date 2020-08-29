<?php

namespace App\Http\Controllers;

use App\CustomField;
use Session;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ApplicationSections;

class CustomFieldsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $this->validate($request, [
            'type' => 'required',
            'label' => 'required',
            'section' => 'required'
        ]);

        $section = ApplicationSections::where('site_url',url())->where('name',$request->section)->first();
        
        $customField = new CustomField;
        $customField->page = $request->page ?? null;
        $customField->site_url = url();
        $customField->type = $request->type;
        $customField->name = str_slug(strtolower($request->label) . ' ' . strtolower($request->type) . ' ' . rand(1, 999), '_');
        $customField->label = $request->label;
        $customField->description = $request->description ?? null;
        $customField->is_required = $request->is_required ? true : false;
        $customField->section = $request->section;
        $customField->section_id = $section->id;
        $customField->attributes = isset($request->attributes) ? json_encode($request->attributes) : null;
        $customField->properties = isset($request->properties) ? json_encode($request->properties) : null;
        $customField->save();

        Session::flash('success', 'Created new custom field - "' . $request->label . '"!');
        return redirect()->back();
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
    public function update(Request $request)
    {
        for($i=0; $i<count($request->customIds); $i++){
            // $rank = $request->rank[$i];
            $customField = CustomField::where('site_url',url())->where('id',$request->customIds[$i])->first();
            // dd($customField);
            $customField->update(['label'=>$request->customFieldLabels[$i],'type'=>$request->customFieldTypes[$i]]);
        }
        return 1;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customField = CustomField::findOrFail($id);
        $label = $customField->label;
        $customField->delete();

        Session::flash('success', 'Deleted custom field - "' . $label . '"!');
        return redirect()->back();
    }
}
