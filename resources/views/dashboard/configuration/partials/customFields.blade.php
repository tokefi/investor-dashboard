<section style="padding: 5% 0%;">
    <div class="col-md-offset-1 col-md-10">
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
        @if(!$customFields->count())
            <div class="alert alert-info text-center">No custom fields created yet!</div>
        @endif
        <table class="table table-borderless table-striped table-responsive">
            <thead>
                <th>Type</th>
                <th>Section</th>
                <th>Label</th>
                <th>Name</th>
                <th>Is Required</th>
                <th>Attributes</th>
                <th>Properties</th>
                <th>Action</th>
            </thead>
            <tbody>
                @foreach ($customFields as $customField)
                    <tr style="font-size: 0.9em;">
                        <td>{{ $customField->type }}</td>
                        <td>{{ $customField->section }}</td>
                        <td>{{ $customField->label }}</td>
                        <td>{{ $customField->name }}</td>
                        <td class="text-center">{{ $customField->is_required ? 'Yes' : 'No' }}</td>
                        <td>
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
                        </td>
                        <td class="text-center">
                            <form name="delete_custom_field" action="{{ route('custom-field.delete', [$customField->id]) }}" method="POST" style="cursor:pointer;">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>