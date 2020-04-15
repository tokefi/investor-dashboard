@extends('layouts.main')

@section('title-section')
{{$user->first_name}} | Dashboard | @parent
@stop

@section('css-section')
	<style type="text/css">
		#userProfileDetails input {
			pointer-events: none;
			background-color: white;
		}
		#user_investments_btn {
			font-size: 1em; 
			letter-spacing: 1px; 
			word-spacing: 2px; 
			padding-right: 1.5rem; 
			padding-left: 1.5rem;
		}
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
			<ul class="list-group">
				<li class="list-group-item">
					<dl class="dl-horizontal">
					<dt></dt>
					<dd><h2>{{$user->first_name}} {{$user->last_name}}</h2> {{-- <a href="/dashboard/users/{{$user->id}}/edit">Edit</a> --}}</dd>
					<dt></dt>
					<dd>{{$user->email}}</dd>
					<dt></dt>
					<dt></dt>
					<dd>{{$user->phone_number}}</dd>
					<hr>
					<dd><a href="{{route('dashboard.users.investments', [$user->id])}}" class="btn btn-primary text-center" title="View user investments">USER INVESTMENTS</a></dd>
					<hr>
					<dt>Active:</dt>
					<dd>@if($user->active) YES @else NO @endif</dd>

					@if($user->gender)
					<dt>Gender:</dt>
					<dd>{{$user->gender}}</dd>
					@endif

					@if($user->date_of_birth)
					<dt>Date of Birth:</dt>
					<dd><time datetime="{{$user->date_of_birth}}">{{$user->date_of_birth->toFormattedDateString()}}</time></dd>
					@endif

					@if($user->active && $user->activated_on)
					<dt>Activated on:</dt>
					<dd>
						<time datetime="{{$user->activated_on}}">{{$user->activated_on->toFormattedDateString()}}</time>
						<time datetime="{{$user->activated_on}}">( {{$user->activated_on->diffInDays()}} days ago )</time>
					</dd>
					@endif

					@if($user->last_login)
					<dt>Last Login:</dt>
					<dd>
						<time datetime="{{$user->last_login}}">{{$user->last_login->toFormattedDateString()}}</time>
						<time datetime="{{$user->last_login}}">( {{$user->last_login->diffInDays()}} days ago )</time>
					</dd>
					@endif
					{{-- <div class="row text-right">
						<div class="form-group">
							<div class="col-sm-offset-1 col-sm-9">
								<a href="{{route('dashboard.users.edit', $user->id)}}" class="btn btn-warning" data-toggle="tooltip" title="Edit profile and bank account details">Edit Details</a>
							</div>
						</div>
					</div> --}}

					<!-- <dt>ID Verification</dt>
					<dd>
						@if($user->verify_id == '2') Id docs have been verified <i class="fa fa-check" style="color:green" data-toggle="tooltip" title="Verified User"></i> 
						@elseif($user->verify_id == '1') Id docs have been submitted for verification <i class="fa fa-hourglass-start" style="color:pink" data-toggle="tooltip" title="Submitted"></i> 
						@elseif($user->verify_id == '0') Did not submit id docs for verification <i class="fa fa-clock-o" data-toggle="tooltip" title="Not submitted"></i> 
						@elseif($user->verify_id == '-1') Verification failed please <i class="fa fa-refresh" style="color:red" data-toggle="tooltip" title="Try Again (verification failed)"></i> 
						@else <i class="fa fa-clock-o" data-toggle="tooltip" title="Not submitted"></i> @endif
					</dd>
					<hr>
					<dt>verify ID</dt>
					<dd>
						@if($user->investmentDoc->where('user_id',$user->id)->last())
						<a href="/{{$user->investmentDoc->where('user_id',$user->id)->last()->path}}">verify ID documents</a>
						@endif
					</dd> -->
					<dt style="margin-top: 1rem;">Change status:</dt>
					<dd style="margin-top: 1rem;">
						@if($user->active && $user->activated_on) <a href="{{route('dashboard.users.deactivate', [$user])}}" class="btn btn-danger" title="Deactivate user account">DEACTIVATE</a>
						@else Not Active <br> <a href="{{route('dashboard.users.activate', [$user])}}" class="btn btn-danger" title="Activate user account">Activate</a>@endif
					</dd>
					<hr>
					<section id="userProfileDetails">
							<div class="row">
								<div class="col-md-10 col-md-offset-1 form-horizontal">
									<fieldset style="padding: 2rem;">
										<div class="row text-right">
											<div class="form-group">
												<div class="col-sm-offset-2 col-sm-9">
													<a href="{{route('dashboard.users.edit', $user->id)}}" class="btn btn-warning" data-toggle="tooltip" title="Edit profile and bank account details">Edit Details</a>
												</div>
											</div>
										</div>
										<h3 class="text-center" style="font-size: 1.7em; word-spacing: 3px;">USER PROFILE DETAILS</h3>
										<br>
										{{-- <div class="row text-right">
											<div class="form-group">
												<div class="col-sm-offset-2 col-sm-9">
													<a href="{{route('dashboard.users.edit', $user->id)}}" class="btn btn-warning" data-toggle="tooltip" title="Edit profile and bank account details">Edit Details</a>
												</div>
											</div> --}}
										{{-- </div> --}}
										<div class="row">
											<div class="form-group">
												{!!Form::label('first_name', 'Name', array('class'=>'col-sm-2 control-label'))!!}
												<div class="col-sm-9">
													<div class="row">
														<div class="col-sm-6">
															{!! Form::text('first_name', $user->first_name, array('placeholder'=>'First Name', 'class'=>'form-control ', 'tabindex'=>'1')) !!}
														</div>
														<div class="col-sm-6">
															{!! Form::text('last_name', $user->last_name, array('placeholder'=>'Last Name', 'class'=>'form-control', 'tabindex'=>'2')) !!}
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="form-group">
												{!!Form::label('email', 'Email', array('class'=>'col-sm-2 control-label'))!!}
												<div class="col-sm-9">
													{!! Form::email('email', $user->email, array('placeholder'=>'you@somewhere.com', 'class'=>'form-control', 'tabindex'=>'4', 'disabled'=>'disabled')) !!}
												</div>
											</div>
										</div>

										<div class="row">
											<div class="form-group">
												{!!Form::label('gender', 'Gender', array('class'=>'col-sm-2 control-label'))!!}
												<div class="col-sm-9">
													{!! Form::select('gender', ['male'=>'Male','female'=>'Female'], $user->gender, array('class'=>'form-control', 'tabindex'=>'7', 'disabled'=>'disabled','style'=>'background-color:white;')) !!}
												</div>
											</div>
										</div>
										<div class="row">
											<div class="form-group">
												{!!Form::label('date_of_birth', 'Your Birth Date', array('class'=>'col-sm-2 control-label'))!!}
												@if($user->date_of_birth)
												<?php $dob_string = $user->date_of_birth->toDateString(); ?>
												@else
												<?php $dob_string = Null; ?>
												@endif
												<div class="col-sm-9">
													{!! Form::input('date', 'date_of_birth', $dob_string , array('class'=>'form-control', 'tabindex'=>'8', 'max'=>'2099-01-01')) !!}
												</div>
											</div>
										</div>
										<div class="row">
											<div class="form-group">
												{!!Form::label('phone_number', 'Mobile', array('class'=>'col-sm-2 control-label'))!!}
												<div class="col-sm-9">
													{!! Form::input('tel', 'phone_number', $user->phone_number, array('placeholder'=>'7276160000', 'class'=>'form-control', 'tabindex'=>'9')) !!}
												</div>
											</div>
										</div>
										<div class="row">
											<div class="form-group">
												{!!Form::label('line_1', 'Address:', array('class'=>'col-sm-2 control-label'))!!}
												<div class="col-sm-9">
													<div class="row">
														<div class="col-sm-6">
															{!! Form::text('line_1', $user->line_1, array('placeholder'=>'line 1', 'class'=>'form-control', 'tabindex'=>'3')) !!}
														</div>
														<div class="col-sm-6">
															{!! Form::text('line_2', $user->line_2, array('placeholder'=>'line 2', 'class'=>'form-control', 'tabindex'=>'4')) !!}
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="form-group">
												<div class="col-sm-offset-2 col-sm-9">
													<div class="row">
														<div class="col-sm-6">
															{!! Form::text('city', $user->city, array('placeholder'=>'City', 'class'=>'form-control', 'tabindex'=>'5')) !!}
														</div>
														<div class="col-sm-6">
															{!! Form::text('state', $user->state, array('placeholder'=>'state', 'class'=>'form-control', 'tabindex'=>'6')) !!}
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="form-group">
												<div class="col-sm-offset-2 col-sm-9">
													<div class="row">
														<div class="col-sm-6">
															{!! Form::text('postal_code', $user->postal_code, array('placeholder'=>'postal code', 'class'=>'form-control', 'tabindex'=>'7')) !!}
														</div>
														<div class="col-sm-6">
															<select name="country" class="form-control country-dropdown" >
																@foreach(\App\Http\Utilities\Country::all() as $country => $code)
																<option data-country-code="{{$code}}" @if($user->country == $country) value="{{$country}}" selected="selected" @else value="{{$country}}" @endif>{{$country}}</option>
																@endforeach
															</select>
															<input type="hidden" name="country_code" class="country-code" value="{{ array_search($user->country, array_flip(\App\Http\Utilities\Country::all())) }}">
														</div>
													</div>
												</div>
											</div>
										</div>
										<br>
										<h3 class="text-center" style="font-size: 1.6em; word-spacing: 3px;">NOMINATED BANK ACCOUNT DETAILS</h3>
										<br>
										<div class="row">
											<div class="form-group">
												{!!Form::label('account_name', 'Account Name', array('class'=>'col-sm-2 control-label'))!!}
												<div class="col-sm-9">
													{!! Form::text('account_name', $user->account_name, array('placeholder'=>'Account name', 'class'=>'form-control', 'tabindex'=>'10')) !!}
												</div>
											</div>
										</div>
										<div class="row">
											<div class="form-group <?php if($errors->first('account_name')){echo 'has-error';}?>">
												{!!Form::label('bsb', 'BSB', array('class'=>'col-sm-2 control-label'))!!}
												<div class="col-sm-9">
													{!! Form::text('bsb', $user->bsb, array('placeholder'=>'BSB', 'class'=>'form-control', 'tabindex'=>'11')) !!}
												</div>
											</div>
										</div>
										<div class="row">
											<div class="form-group">
												{!!Form::label('account_number', 'Account Number', array('class'=>'col-sm-2 control-label'))!!}
												<div class="col-sm-9">
													{!! Form::text('account_number', $user->account_number, array('placeholder'=>'Account number', 'class'=>'form-control', 'tabindex'=>'12')) !!}
												</div>
											</div>
										</div>
										<div class="row">
											<div class="form-group">
												{!!Form::label('tfn', 'TFN', array('class'=>'col-sm-2 control-label'))!!}
												<div class="col-sm-9">
													{!! Form::text('tfn', $user->tfn, array('placeholder'=>'tfn', 'class'=>'form-control', 'tabindex'=>'12')) !!}
												</div>
											</div>
										</div>
										<br>
									</fieldset>
								</div>
							</div>
						</section>
						<hr>
					<div class="text-center">
						{{-- <a href="{{route('dashboard.users.investments', [$user_id])}}" class="btn btn-primary text-center" id="user_investments_btn" ">USER INVESTMENTS</a> --}}
					</div>
				</dl>
			</li>
		</ul>
{{--  		<ul class="list-group">
			@if($user->investments->count())
			@foreach($user->investments as $project)
			<a href="{{route('dashboard.projects.show', [$project])}}" class="list-group-item">{{$project->title}} <i class="fa fa-angle-right pull-right"></i></a>
			@endforeach
			@else
			<li class="list-group-item text-center alert alert-warning">Not Shown any Interest </li>
			@endif
		</ul> --}}
	</div>
</div>
@stop
