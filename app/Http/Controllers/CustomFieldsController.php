<?php

namespace App\Http\Controllers;

use App\CustomField;
use Session;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ApplicationSections;
use App\RadioButtonCustomField;

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
        if($request->radioField !== ""){
            $field = CustomField::where('site_url',url())->where('name',$request->radioField)->first();
            $button = RadioButtonCustomField::create(['radio_custom_field'=>$field->id,'label' => $request->label,'value'=>$request->buttonValue,'site_url'=>url()]);

            Session::flash('success', 'Created radio button - "' . $request->label . '"!');
            return redirect()->back();
        }else{
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
            if($request->radioMasterField){
                $field = RadioButtonCustomField::where('id',$request->radioMasterField)->where('site_url',url())->first();
                $customField->radio_master_field = $field->id;
                $customField->show_custom_field = 0;
            }
            $customField->attributes = isset($request->attributes) ? json_encode($request->attributes) : null;
            $customField->properties = isset($request->properties) ? json_encode($request->properties) : null;
            $customField->save();

            Session::flash('success', 'Created new custom field - "' . $request->label . '"!');
            return redirect()->back();
        }
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
            // dd($section);
            $customField->update(['label'=>$request->customFieldLabels[$i],'type'=>$request->customFieldTypes[$i],'is_required'=>$request->agent_type[$i]]);
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
    public function updateSection(Request $request)
    {
        $customField = CustomField::where('site_url',url())->where('id',$request->customIds)->first();
        $section = ApplicationSections::where('site_url',url())->where('name',$request->customFieldSections)->first();
        $customField->update(['section'=>$section->name,'section_id'=>$section->id]);
        Session::flash('success', 'Section label updated successfully!!!');
        return redirect()->back();
    }
    public function showCustomField(Request $request)
    {
        $fields = CustomField::where('radio_master_field',$request->customFieldId)->get();
        $customField = array();
        $hideField = array();
        $mainField = RadioButtonCustomField::where('id',$request->customFieldId)->first() ;
        $otherField = RadioButtonCustomField::where('radio_custom_field', $mainField->radio_custom_field)->lists('id')->toArray();
        array_splice($otherField, array_search($mainField->id, $otherField ), 1);
        $hideFields = CustomField::where('section_id',$fields[0]->section_id)->WhereIn('radio_master_field',$otherField)->get();
        foreach ($hideFields as $field) {
            array_push($hideField, $field->name);
        }
        foreach ($fields as $field) {
            array_push($customField, $field->name);
        }
        return response()->json([
            'status' => '1','customField' => $customField, 'hideFields' => $hideField]);             
    }
}
