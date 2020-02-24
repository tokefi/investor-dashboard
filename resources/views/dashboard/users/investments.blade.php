@extends('layouts.main')

@section('title-section')
{{$user->first_name}} Investments | Dashboard | @parent
@stop

@section('css-section')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css">
<style type="text/css">
	#investmentsTable th {
		text-align: center;
	}
	#projects_dropdown {
		margin-left: auto;
		margin-right: auto;
	}
	.select-side {
    &:before {
      border-left: solid 1px lightgrey;
      content : "";
      position: absolute;
      left    : 0;
      bottom  : 0;
      height  : 100%;
      width   : 1px;  /* or 100px */
    }
	/*.dropdown-menu {
    width: 300px !important;
    height: 400px !important;
	}*/
</style>
@stop

@section('content-section')
<div class="container">
	<br>
	<div class="row">
		{{--<div class="col-md-2">
			@include('dashboard.includes.sidebar', ['active'=>2])
		</div>--}}
		<div class="col-md-12">
			@if (Session::has('message'))
			{!! Session::get('message') !!}
			@endif
			<ul class="list-group">
				<li class="list-group-item">
					<div class="text-center">
						<h3>{{$user->first_name}} {{$user->last_name}}<br><small>{{$user->email}}</small></h3>
					</div>
				</li>
			</ul>
			<ul class="list-group">
				<li class="list-group-item">
					<form action="" rel="form" method="POST" enctype="multipart/form-data" id="myform" class="invest-for-user-form">
						{!! csrf_field() !!}
						<div class="text-center">
							<h3 class="text-center">Invest for user</h3>
							@if ($errors->has())
								<br>
								<div class="alert alert-danger">
									@foreach ($errors->all() as $error)
									{{ $error }}<br>
									@endforeach
								</div>
								<br>
								@endif
							<br>
							<div class="row">
							  	<div class="col-md-4 col-md-offset-2">
									{{-- <div class="dropdown" id="projects_dropdown">
                                      <button class="btn btn-primary btn-block dropdown-toggle dropdown-btn" type="button" data-toggle="dropdown">Select Project
                                      <span class="caret"></span></button>
                                          <ul class="dropdown-menu">
                                            @foreach($projects as $project)
                                                <li><a href="#">{{$project->title}}</a></li>
                                            @endforeach
                                          </ul>
                                    </div> --}}
									<div class="form-group">
										<select class="form-control" id="project_to_invest">
											<div class="select-side">
												<i class="glyphicon glyphicon-menu-down blue"></i>
											</div>
											@foreach($projects as $project)
												<option value="{{$project->id}}">{{$project->title}}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-md-4">
									<div>
										<input type="hidden" name="user_id" value="{{$user->id}}">
										<input type="hidden" name="project_id" id="project_id" value="">
										<input type="submit" name="submitoffer" class="btn btn-primary btn-block"
											   value="Submit" id="offerSubmit">
									</div>
								</div>
							</div>
							<br>
						</div>
					</form>
				</li>
			</ul>
			<h3 class="text-center">User Investments</h3>
			<div class="table-responsive text-center">
				<table class="table table-bordered table-striped" id="investmentsTable">
					<thead>
						<tr>
							<th>Project Name</th>
							<th>Investment Amount</th>
							<th>Investment Date</th>
							<th>Investment status</th>
							<th>Link to share certificate</th>
							<th>Returns received</th>
							<th>Tax and Accounting Docs</th>
						</tr>
					</thead>
					<tbody>
						@if($investments->count())
						@foreach($investments as $investment)
							<tr @if($investment->is_cancelled) style="color: #CCC;" @endif>
								<td>{{$investment->project->title}}</td>
								<td>${{number_format($investment->amount)}}</td>
								<td>{{$investment->created_at->toFormattedDateString()}}</td>
								<td>
									@if($investment->accepted)
									Shares issued
									@elseif($investment->money_received)
									Funds committed
									@elseif($investment->investment_confirmation)
									Approved
									@else
									Applied
									@endif
								</td>
								<td>
									@if($investment->is_repurchased)
									<strong>Investment is repurchased</strong>
									@else
									@if($investment->is_cancelled)
									<strong>Investment record is cancelled</strong>
									@else
									@if($investment->accepted)
									<a href="{{route('user.view.share', [base64_encode($investment->id)])}}" target="_blank">Share Certificate</a>
									@else
									NA
									@endif
									@endif
									@endif
								</td>
								<td></td>
								<td></td>
							</tr>
						@endforeach
						@endif
					</tbody>				
				</table>
			</div>
		</div>
	</div>
</div>
@stop

@section('js-section')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
	
	$(document).ready(function(){
		var usersTable = $('#investmentsTable').DataTable({
			"order": [[5, 'desc'], [0, 'desc']],
			"iDisplayLength": 50
		});

		default_project_value = $('#project_to_invest').val();
		$('#project_id').val(default_project_value);

		$('#project_to_invest').change(function () {
	        var project_id = this.val();
	        // alert(project_id);
	        $('#project_id').val(project_id);
	    });

		$('.invest-for-user-form').submit(function (e) {
			e.preventDefault();
			let userId = '{{$user->id}}';
			let projectId = $('#project_to_invest').val();
			if (projectId == '') {
				alert('Select Project!');
				return;
			}
			location.href = '/projects/' + projectId + '/interest?apid=' + projectId + '&auid=' + userId;
		})
	});
</script>
@stop