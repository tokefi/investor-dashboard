@extends('layouts.main')

@section('title-section')
Import Contacts | Dashboard | @parent
@stop

@section('meta')
<meta name="csrf-token" content="{{csrf_token()}}" />
@endsection

@section('css-section')
<style type="text/css">
</style>
@stop

@section('content-section')
<div class="container">
	<br>
	<div class="row">
		{{--<div class="col-md-2">
			@include('dashboard.includes.sidebar', ['active'=>10])
		</div>--}}
		<div class="col-md-12">
            <section>
                @if (Session::has('message'))
                {!! Session::get('message') !!}
                @endif
                <div class="row text-center ">
                    <h2>Import Clients file</h2>
                    <p>Download sample file <a href="{{ route('dashboard.import.clients.sample') }}" target="_blank">HERE</a></p>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3 col-sm-6 col-sm-offset-3 text-center">
                        <br>
                        <p><small><i>Please select a CSV file containing user details.</i></small></p><br>
                        {!! Form::open(array('route'=>['dashboard.import.clients.csv'], 'files' => true, 'method'=>'POST', 'class'=>'form-horizontal', 'role'=>'form')) !!}
                             <div class="input-group">
                                <label class="input-group-btn">
                                    <span class="btn btn-primary" style="padding: 10px 12px;">
                                        Browse&hellip; <input type="file" name="clients_file" id="clients_file" class="form-control" style="display: none;">
                                    </span>
                                </label>
                                <input type="text" class="form-control" id="clients_filename" name="clients_filename" readonly>
                            </div><br>
                            {!! Form::submit('Upload', array('class'=>'btn btn-primary col-md-4 col-md-offset-4', 'id'=>'submit_csv_file')) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </section>	
        </div>
    </div>
</div>
@stop

@section('js-section')

    <script type="text/javascript">
        $(document).ready(function(e){
            $('#clients_file').change(function(){
                var file = $('#clients_file')[0].files[0];
                if (file) {
                    fileExtension = (file.name).substr(((file.name).lastIndexOf('.') + 1)).toLowerCase();
                    if(fileExtension == 'csv' ) {
                        $('#clients_filename').val(file.name);
                    } else {
                        $('#clients_file').val('');
                        $('#clients_filename').val('');
                        alert('Only .csv files allowed.');
                   }
                }
            });

            $('#submit_csv_file').on('click', function() {
                if($('#clients_file').val()) {
                    $(this).attr('disabled', 'disabled');
                    $(this).parents('form').submit();
                }
            });
        });
    </script>
@stop