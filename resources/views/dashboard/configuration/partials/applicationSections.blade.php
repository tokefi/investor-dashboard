<section style="padding: 5% 0%;">
    <div class="col-md-offset-1 col-md-10">
        @if(!$sections->count())
        <div class="well text-center">
            <form action="{{ route('application-section.create') }}" method="POST">
                {{ csrf_field() }}
                <button type="submit" class="btn btn-primary">Add basic sections</button>
            </form>
        </div>
        @else
        <h2 class="text-center">Application form sections</h2>
        <div class="well">
            <form action="{{ route('application-section.store') }}" method="POST">
                {{ csrf_field() }}
                <div class="row form-group">
                    <div class="col-sm-12" style="margin-bottom: 20px;">
                        <label for="label">Label<sup class="text-danger">*<sup></label>
                            <input type="text" class="form-control" id="label" name="label" placeholder="Enter label" required>
                        </div>
                        <div class="col-sm-12" style="margin-bottom: 20px;">
                            <label for="description">Description</label>
                            <textarea class="form-control" rows="2" id="description" name="description" placeholder="Enter Description"></textarea>
                        </div>    
                    </div>
                    <div class="text-right">
                        <input hidden name="page" value="application_form">
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
            @endif
            <div id="reorderMsg" class="hide alert alert-info text-center">Successfully updated the sections!!!</div>
            @if(!$sections->count())
            <div class="alert alert-info text-center">No sections created yet!</div>
            @endif

            <table class="table table-borderless table-striped table-responsive ">
                <thead>
                    <th>Section Label</th>
                    <th class="pull-left">Rank</th>
                    <th class="text-center">Action</th>
                </thead>
                <tbody>
                    @foreach ($sections as $section)
                    <tr style="font-size: 0.9em;">
                        <td><input type="text" name="sectionLabels[]" value="{{ $section->label }}" style="border:0px;  background-color: transparent; width: 100%;"></td>
                        <td class="pull-left"><input type="integer" name="rank[]" value="{{ $section->rank }}" style="border-color:#fff; border:0px;  background-color: transparent;">
                            <input type="hidden" name="sectionIds[]" value="{{ $section->name }}">
                        </td>
                        <td class="text-center">

                            {{-- <div class="@if($section->name === 'investment_details' || $section->name === 'investing_type' || $section->name === 'contact_details' || $section->name === 'nominated_bank_account' || $section->name === 'interested_to_buy' || $section->name === 'signature') hide  @endif">
                            <form action="{{ route('application-section.delete', [$section->id]) }}" method="POST" style="cursor:pointer;">

                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fa fa-trash"></i></button>
                            </form>
                            <div> --}}

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary pull-right" id="reorder">Update</button>
            </div>
        </section>