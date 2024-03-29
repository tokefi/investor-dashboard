<section style="padding: 5% 0%;">
    <div class="col-md-offset-1 col-md-10" style="padding-bottom: 2em;">
        <h2 class="text-center">Application form custom fields</h2>
        
        <div class="well">
            <form action="{{ route('custom-field.store') }}" method="POST">
                {{ csrf_field() }}
                <div class="row form-group">
                    <div class="col-sm-4" style="margin-bottom: 20px;">
                        <label for="type">Select field type<sup class="text-danger">*<sup></label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="">-- Select --</option>
                                <option value="text">Text</option>
                                <option value="date">Date</option>
                            </select>
                        </div>
                        <div class="col-sm-4" style="margin-bottom: 20px;">
                            <label for="label">Label<sup class="text-danger">*<sup></label>
                                <input type="text" class="form-control" id="label" name="label" placeholder="Enter label" required>
                            </div>

                            <div class="col-sm-4" style="margin-bottom: 20px;">
                                <label for="section">Select section<sup class="text-danger">*<sup></label>
                                    <select class="form-control" id="type" name="section" required>
                                        <option value="">-- Select --</option>
                                        @foreach ($sections as $section)
                                        <option value="{{ $section->name }}">{{ $section->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-12" style="margin-bottom: 20px;">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" rows="2" id="description" name="description" placeholder="Enter Description"></textarea>
                                </div>    
                                <div class="col-sm-12">
                                    <input type="checkbox" name="is_required" id="is_required" />
                                    <label for="is_required">Is Required ?</label>
                                </div>
                                <div class="col-sm-12">
                                    <input type="checkbox" name="is_conditional" id="is_conditional" checked />
                                    <label for="is_conditional">Is conditional on checkbox ?</label>
                                </div>
                                <div class="col-sm-12">
                                    <input type="checkbox" name="properties[is_retail_only]" id="is_retail_only" />
                                    <label for="is_retail_only">Show on only retail project ?</label>
                                </div>
                            </div>
                            <div class="text-right">
                                <input hidden name="page" value="application_form">
                                <button type="submit" class="btn btn-primary">Create</button>
                            </div>
                        </form>
                    </div>
                    <div id="custom-fields-msg" class="hide alert alert-info text-center">Successfully update the custom fields!!!</div>
                    @if(!$customFields->count())
                    <div class="alert alert-info text-center">No custom fields created yet!</div>
                    @endif
                    <table class="table table-borderless table-striped table-responsive">
                        <thead>
                            <th>Type</th>
                            <th>Section label</th>
                            <th>Field Label</th>
                            <th>Checkbox</th>
                            <th>Is Required</th>
                            {{-- <th>Attributes</th> --}}
                            {{-- <th>Properties</th> --}}
                            <th>Action</th>
                        </thead>
                        <tbody>
                            @foreach ($customFields as $customField)
                            <tr style="font-size: 0.9em;">
                                <td><select class="form-control" id="type" name="customFieldTypes[]" required style="border:0px solid !important ; background-color: transparent; box-shadow: inset 0 1px 1px rgba(0,0,0,0);    height: 22px; padding: 0px 12px;">
                                    <option value="{{ $customField->type }}">{{ $customField->type }}</option>
                                    <option value="text">Text</option>
                                    <option value="date">Date</option>
                                </select>
                            </td>
                            <td>
                                <form action="{{route('custom-field.section.update', [$customField->id])}}" method="POST">
                                    {{method_field('PATCH')}}
                                    {{csrf_field()}}
                                    <select class="form-control" id="type" name="customFieldSections" required style="border:0px solid !important ; background-color: transparent; box-shadow: inset 0 1px 1px rgba(0,0,0,0);    height: 22px; padding: 0px 12px;" onchange="this.form.submit()">
                                        <option value="@if(isset($customField->applicationSection)){{  $customField->applicationSection->name }} @endif">@if(isset($customField->applicationSection)){{  $customField->applicationSection->label }} @endif</option>
                                        @foreach ($sections as $section)
                                        <option value="{{ $section->name }}">{{ $section->label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="customIds" value="{{ $customField->id }}">
                                </form>
                            </td>
                            <td><input type="text" name="customFieldLabels[]" value="{{ $customField->label }}" style="border:0px;  background-color: transparent;"> </td>
                            <td>@if($customField->show_checkbox) <input class="checkbox_custom" type="checkbox" name="individual" field-id="{{ $customField->id }}" checkbox-id="1" @if($customField->isConditional->where('checkbox_id',1)->first()) @if( $customField->isConditional->where('checkbox_id',1)->first()->is_linked) checked @else @endif @else checked <?php App\CheckboxConditional::create(['checkbox_id'=>1,'custom_field_id'=>$customField->id,'is_linked'=>1]); ?> @endif> Individual Investor <br><input class="checkbox_custom" type="checkbox" name="joint" field-id="{{ $customField->id }}" checkbox-id="2" @if($customField->isConditional->where('checkbox_id',2)->first()) @if( $customField->isConditional->where('checkbox_id',2)->first()->is_linked) checked @else @endif @else checked <?php App\CheckboxConditional::create(['checkbox_id'=>2,'custom_field_id'=>$customField->id,'is_linked'=>1]); ?> @endif> Joint Investor <br><input class="checkbox_custom" type="checkbox" name="company" field-id="{{ $customField->id }}" checkbox-id="3" @if($customField->isConditional->where('checkbox_id',3)->first()) @if( $customField->isConditional->where('checkbox_id',3)->first()->is_linked) checked @else @endif @else checked <?php App\CheckboxConditional::create(['checkbox_id'=>3,'custom_field_id'=>$customField->id,'is_linked'=>1]); ?> @endif> Company, Trust or SMSF @endif </td>
                            <td class="text-center">
                                <input type="checkbox" name='agent_type[]' class="toggle-elements" action="expected_return_{{ $customField->id }}" autocomplete="off"  data-size="mini" @if($customField->is_required) checked value="1" @else value="0" @endif id="expected_return_{{ $customField->id }}">
                            </td>
                            {{-- <td>
                                @if ($customField->attributes)
                                @foreach ($customField->attributes as $key => $attribute)
                                {{ $key }} : {{ $attribute == 'on' ? 'Yes' : $attribute }} <br />
                                @endforeach
                                @endif
                            </td>
                            <td>
                                @if ($customField->properties)
                                @foreach ($customField->properties as $key => $property)
                                {{ $key }} : {{ $property == 'on' ? 'Yes' :  $property}} <br />
                                @endforeach
                                @endif
                            </td> --}}
                            <td class="text-center">
                                <form name="delete_custom_field" action="{{ route('custom-field.delete', [$customField->id]) }}" method="POST" style="cursor:pointer;">
                                    {{ csrf_field() }}
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <input type="hidden" name="customIds[]" value="{{ $customField->id }}">
                        @endforeach
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary pull-right" id="updateCustomField">Update</button>
                
            </div>
        </section>