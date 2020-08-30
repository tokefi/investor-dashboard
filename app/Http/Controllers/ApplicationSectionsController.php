<?php

namespace App\Http\Controllers;

use App\ApplicationSections;
use Illuminate\Http\Request;
use Session;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ApplicationSectionsController extends Controller
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
            'label' => 'required'
        ]);
        $section = ApplicationSections::where('site_url',url());
        $count = $section->count();
        //add basic sections
        if(!$section->count()){
            //section investment details
            $section = new ApplicationSections;
            $section->site_url = url();
            $section->name = 'investment_details';
            $section->label = 'Investment Details';
            $section->description =  null;
            $section->rank = 1;
            $section->save();
            // section Investing Type  
            $section = new ApplicationSections;
            $section->site_url = url();
            $section->name = 'investing_type';
            $section->label = 'Are you Investing as';
            $section->description =  null;
            $section->rank = 2;
            $section->save();
        // Contact Details section
            $section = new ApplicationSections;
            $section->site_url = url();
            $section->name = 'contact_details';
            $section->label = 'Contact Details';
            $section->description =  null;
            $section->rank = 3;
            $section->save();
        // Nominated Bank Account
            $section = new ApplicationSections;
            $section->site_url = url();
            $section->name = 'nominated_bank_account';
            $section->label = 'Nominated Bank Account';
            $section->description =  'Please enter your bank account details where you would like to receive any Dividend or other payments related to this investment';
            $section->rank = 4;
            $section->save();
            // Interested to buy
            $section = new ApplicationSections;
            $section->site_url = url();
            $section->name = 'interested_to_buy';
            $section->label = 'Interested To Buy';
            $section->description =  '';
            $section->rank = 5;
            $section->save();
            // Interested to buy
            $section = new ApplicationSections;
            $section->site_url = url();
            $section->name = 'signature';
            $section->label = 'Signature';
            $section->description =  '';
            $section->rank = 6;
            $section->save();
            Session::flash('success', 'Created basic sections !');
        return redirect()->back();
        }
       
        $section = ApplicationSections::where('name','interested_to_buy')->where('site_url',url())->first();
        $section->update(['rank'=>$count]);
        $section = ApplicationSections::where('site_url',url())->where('name','signature')->first();
        $section->update(['rank'=>$count+1]);

        // add custom section
        $newSection = new ApplicationSections;
            $newSection->site_url = url();
            $newSection->name = 'section_'. rand(1, 999);
            $newSection->label = $request->label;
            $newSection->description =  $request->description ?? null;
            $newSection->rank = $count-1;
            $newSection->save();

        Session::flash('success', 'Created new section - "' . $request->label . '"!');
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
        $section = ApplicationSections::findOrFail($id);
        $label = $section->label;
        $section->delete();

        Session::flash('success', 'Deleted section - "' . $label . '"!');
        return redirect()->back();
    }

    // reorder the rank of sections
    public function reorder(Request $request)
    {
        // dd($request->rank);
        for($i=0; $i<count($request->rank); $i++){
            $rank = $request->rank[$i];
            $section = ApplicationSections::where('site_url',url())->where('name',$request->sectionIds[$i])->first();
            $section->update(['rank'=>$rank,'label'=>$request->sectionLabels[$i]]);
        }
        return 1;
    }
}
