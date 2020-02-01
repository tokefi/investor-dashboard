@extends('layouts.main')

@section('title-section')
Users | Dashboard | @parent
@stop

@section('css-section')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css">
@stop

@section('content-section')
<div class="container">
	<br>
	<div class="row">
		<div class="col-md-2">
			@include('dashboard.includes.sidebar', ['active'=>2])
		</div>
		<div class="col-md-10">
			@if (Session::has('message'))
			{!! Session::get('message') !!}
			@endif
			<div class="table-responsive">
				<form action="" method="GET" name="search_filter_form" id="search_filter_form">
					{{ csrf_field() }}
					<div class="row">
						<div class="col-sm-4 col-sm-offset-8">
							<label><small><i class="text-dark-grey">Search by name, email and phone number.</i></small></label>
							<div class="input-group">
								<input type="text" placeholder="Search..." name="search" id="search"
									   class="form-control"
									   value="{{ request('search') }}"
									   title="Search on name, email and phone number.">
								<div class="input-group-btn">
									<button class="btn btn-primary form-control" type="submit">
										<i class="glyphicon glyphicon-search"></i>
									</button>
								</div>
							</div>
						</div>
					</div>
				</form>
				<br>
				<table class="table table-bordered table-striped" id="usersTable">
					<thead>
					<tr>
						<th>
							<a href="{{route('dashboard.users')}}?search={{urlencode(request('search'))}}&field=id&sort={{request('sort','asc')=='asc'?'desc':'asc'}}"
							>#</a>
							{{request('field')=='id'?(request('sort','asc')=='asc'?'&#9652;':'&#9662;'):''}}
						</th>
						<th><a href="#">Details</a></th>
						<th><a href="#">Invest for user</a></th>
						<th><a href="#">Notes</a></th>
						<th>
							<a href="{{route('dashboard.users')}}?search={{urlencode(request('search'))}}&field=last_login&sort={{request('sort','asc')=='asc'?'desc':'asc'}}"
							   >Last Login</a>
							{{request('field')=='last_login'?(request('sort','asc')=='asc'?'&#9652;':'&#9662;'):''}}
						</th>
						<th>
							<a href="{{route('dashboard.users')}}?search={{urlencode(request('search'))}}&field=activated_on&sort={{request('sort','asc')=='asc'?'desc':'asc'}}"
							   >Active</a>
							{{request('field')=='activated_on'?(request('sort','asc')=='asc'?'&#9652;':'&#9662;'):''}}
						</th>
						<th>
							<a href="{{route('dashboard.users')}}?search={{urlencode(request('search'))}}&field=created_at&sort={{request('sort','asc')=='asc'?'desc':'asc'}}"
							   >Registration</a>
							{{request('field')=='created_at'?(request('sort','asc')=='asc'?'&#9652;':'&#9662;'):''}}
						</th>
					</tr>
					</thead>
					<tbody>
						@foreach($users as $user)
						<tr class="@if(!$user->active) inactive @endif">
							<td>{{$user->id}}</td>
							<td>
								<a href="{{route('dashboard.users.show', $user)}}">{{$user->first_name}} {{$user->last_name}}</a>
								@if($user->verify_id == '2')&nbsp;&nbsp;<i class="fa fa-check" style="color:green" data-toggle="tooltip" title="Verified User"></i> @elseif($user->verify_id == '1') &nbsp;&nbsp;<i class="fa fa-hourglass-start" style="color:pink" data-toggle="tooltip" title="Submitted"></i> @elseif($user->verify_id == '0') &nbsp;&nbsp;<i class="fa fa-clock-o" data-toggle="tooltip" title="Not submitted"></i> @elseif($user->verify_id == '-1') &nbsp;&nbsp;<i class="fa fa-refresh" style="color:red" data-toggle="tooltip" title="Try Again (verification failed)"></i> @else &nbsp;&nbsp;<i class="fa fa-clock-o" data-toggle="tooltip" title="Not submitted"></i> @endif
								<br>
								{{$user->email}} <a href="mailto:{{$user->email}}"><i class="fa fa-envelope-o" data-toggle="tooltip" title="Send Email"></i></a>
								<br>
								{{$user->phone_number}}
								<br>
								<a href="{{route('dashboard.users.investments', [$user])}}">Investments <i class="fa fa-angle-double-right"></i></a>
								<a href="{{route('dashboard.users.document', [$user])}}">KYC @if($user->idDoc && $user->idDoc->verified == '1') <i class="fa fa-check-circle" aria-hidden="true"></i> @endif <i class="fa fa-angle-double-right"></i></a>
							</td>
							<td>
								<select class="form-control project_to_invest" id="project_to_invest_{{$user->id}}" data="{{$user->id}}" style="width: 10em;"> 
									<option disabled="" selected="" style="width: 10em;">Select Project</option>
								  	@foreach($projects as $project)
								    	<option id="user_{{$user->id}}" value="{{$project->id}}">{{$project->title}}</option>
								    @endforeach
								    {{-- <input type="hidden" class="user_id" name="user_id" value="{{$user->id}}"> --}}
								</select>
								<br>
								<div id="btn_{{$user->id}}"></div>
								{{-- <a class="btn btn-primary" style="width: 10em;" href="{{route('projects.interest', [1])}}?user_id={{$user->id}}">Next >></a> --}}
							</td>
							<td>
								<?php
								$note=$user->notes->last();
								$note_content = null;
								if($note) {
									$note_content = $note->content;
								}
								?>
								{!! Form::open(array('route'=>'notes.store', 'class'=>'form-horizontal', 'role'=>'form')) !!}
								{!! Form::textarea('content', $note_content, array('placeholder'=>'note', 'class'=>'form-control note-content', 'rows'=>'3')) !!}
								{!! Form::hidden('user_id', $user->id) !!}
								{!! Form::close() !!}
							</td>
							<td>@if($user->last_login) <time datetime="{{$user->last_login}}">{{$user->last_login->diffForHumans()}}</time> @else Not yet @endif</td>
							<td>@if($user->active && $user->activated_on)<time datetime="{{$user->activated_on}}">{{$user->activated_on->toFormattedDateString()}}</time> <br> <a href="{{route('dashboard.users.deactivate', [$user])}}">Deactivate</a>@else Not Active <br> <a href="{{route('dashboard.users.activate', [$user])}}">Activate</a>@endif</td>
							<td><time datetime="{{$user->created_at}}">{{$user->created_at->toFormattedDateString()}}</time></td>
							{{-- <td>@foreach($user->roles as $role) {{$role->role}}<br> @endforeach</td> --}}
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			<div>
				<span class="pull-left">Total Users {!! $users->total() !!}</span>
			</div>
			<div class="pull-right"> {!! $users->render() !!} <br>
			</div>
		</div>
	</div>
</div>
@stop

@section('js-section')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script type="text/javascript">
	$(document).ready(function(){
		onChangeMsg();
		function onChangeMsg() {
			$('.note-content').change(function() {
				swal("User Note Added Successfully!", {
					icon: "success",
					buttons: false,
					timer: 1350,
				});
			})
		}

		$('.note-content').blur(function () {
			var form = $(this).parent();
			$.ajax({
				url     : form.attr('action'),
				type    : form.attr('method'),
				dataType: 'json',
				data    : form.serialize(),
				success : function( data ) {
					onChangeMsg();
				},
				error   : function( xhr, err ) {
					alert('Error');
				}
			});
		});

		$('.project_to_invest').on('change', function(){
	  			var project_id = $(this).val();
	  			var user_id = $(this).attr('data');
	  			$('#btn_'+user_id).html('<a class="btn btn-primary" style="width: 10em;" href="/projects/'+project_id+'/interest?apid='+project_id+'&auid='+user_id+'">Next >></a>');
		});
	});
</script>
@stop
