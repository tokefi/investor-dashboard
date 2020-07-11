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
<div class="container-fluid">
	<br>
	<div class="row">
		{{--<div class="col-md-2">
			@include('dashboard.includes.sidebar', ['active'=>2])
		</div>--}}
		
		<div class="col-md-12">
			<div class="row">
				@if (Session::has('message'))
				{!! Session::get('message') !!}
				@endif
			</div>
			<ul class="nav nav-tabs" style="margin-top: 0.8em; width: 100%;">
				<li class="active" style="width: 50%;">
					<a data-toggle="tab" href="#profile_tab" style="padding: 0em 2em"><h3 class="text-center">Profile</h3></a>
				</li>
				<li style="width: 50%;">
					<a data-toggle="tab" href="#kyc_tab" style="padding: 0em 2em"><h3 class="text-center">KYC</h3></a>
				</li>
			</ul>
			<div class="tab-content">
				<div id="profile_tab" class="tab-pane fade in active" style="overflow: auto; margin-top: 1em;">
					<ul class="list-group">
						<li class="list-group-item">
							<dl class="dl-horizontal">
								<dt></dt>
								<dd><h2>{{$user->first_name}} {{$user->last_name}}</h2></dd>
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
							</dl>
						</li>
					</ul>
				</div>
				<div id="kyc_tab" class="tab-pane fade" style="margin-top: 1em; overflow: auto;">
					<ul class="list-group">
						<li class="list-group-item">
							<dl class="dl-horizontal">
								<dt></dt>
								<dd><h2>{{$user->first_name}} {{$user->last_name}}</h2></dd>
								<dt></dt>
								<dd>
									<div class="row">
										<div class="col-md-7">
											{{$user->email}}
										</div>
										<div class="col-md-5">
											<a href="{{route('users.edit', $user)}}">edit</a>
										</div>
									</div>
								</dd>
								<hr>
								<dt></dt>
								<dd><h4>Upload users id documents</h4></dd><br>
								<dt></dt>
								<dd>
									@if($user->idDoc)
									<h4>User is investing as<b style="color: blue;">{{$user->idDoc->investing_as}}</b></h4><br>
									<a href="{{$user->idDoc->registration_site}}/{{$user->idDoc->path}}">KYC Doc</a>
									@if($user->idDoc->investing_as == 'Joint Investor')
									<p>Joint Investor Name:<br><b>{{$user->idDoc->joint_first_name}} {{$user->idDoc->joint_last_name}}</b></p>
									<a href="{{$user->idDoc->registration_site}}/{{$user->idDoc->joint_id_path}}">Joint Investor Doc</a>
									@endif
									<hr>
									@if($user->idDoc->verified == '1')
									Verified <i class="fa fa-check-circle" aria-hidden="true"></i>
									@elseif($user->idDoc->verified == '-1')
									<span style="color: red;">Verification Failed</span>
									@else
									Not-Verified
									@endif
									@endif
									@if(!$user->idDoc || $user->idDoc->verified == '-1')
									<form class="form-group" action="{{route('dashboard.users.document.upload',[$user->id])}}" method="POST" enctype="multipart/form-data" rel="form">
										{{ csrf_field() }}
										<div class="row " id="section-2">
											<div class="col-md-12">
												<div >
													<h5>Individual/Joint applications - refer to naming standards for correct forms of registrable title(s)</h5>
													<br>
													<h4>Are you Investing as</h4>
													<input type="radio" name="investing_as" value="Individual Investor" checked> Individual Investor<br>
													<input type="radio" name="investing_as" value="Joint Investor"> Joint Investor<br>
													<input type="radio" name="investing_as" value="Trust or Company"> Trust or Company<br>
													<hr>
												</div>

											</div>
										</div>
										<div class="row " id="section-3">
											<div class="col-md-12">
												<div style="display: none;" id="company_trust">
													<label>Company or Trust Name</label>
													<div class="row">
														<div class="col-md-9">
															<input type="text" name="investing_company_name" class="form-control" placeholder="Trust or Company" required disabled="disabled">
														</div>
													</div><br>
												</div>
												<div id="normal_name">
													<label>Given Name(s)</label>
													<div class="row">
														<div class="col-md-9">
															<input type="text" name="first_name" class="form-control" placeholder="First Name" required @if(!Auth::guest() && $user->first_name) value="{{$user->first_name}}" readonly @endif>
														</div>
													</div><br>
													<label>Surname</label>
													<div class="row">
														<div class="col-md-9">
															<input type="text" name="last_name" class="form-control" placeholder="Last Name" required @if(!Auth::guest() && $user->last_name) value="{{$user->last_name}}" readonly @endif>
														</div>
													</div><br>
												</div>
												<div style="display: none;" id="joint_investor">
													<label>Joint Investor Details</label>
													<div class="row">
														<div class="col-md-6">
															<input type="text" name="joint_investor_first" class="form-control" placeholder="Investor First Name" required disabled="disabled">
														</div>
														<div class="col-md-6">
															<input type="text" name="joint_investor_last" class="form-control" placeholder="Investor Last Name" required disabled="disabled">
														</div>
													</div>
													<br>
													<hr>
												</div>
											</div>
										</div>
										<div class="row " id="section-4">
											<div class="col-md-12">
												<div id="trust_doc" style="display: none;">
													<label>Trust or Company DOCS</label>
													<input type="file" name="trust_or_company_docs" class="form-control" disabled="disabled" required><br>

													<p>Please upload the first and last pages of your trust deed or Company incorporation papers</p>
												</div>
												<div id="normal_id_docs">
													@if(!Auth::guest() && $user->investmentDoc->where('user_id', $user->id AND 'type','normal_name'))
													<div class="row">
														<div class="col-md-6">
														</div>
													</div>
													<br>
													@else
													@endif
													<label>ID DOCS</label>
													<input type="file" name="user_id_doc" class="form-control" required><br>
													<p>If you have not completed your verification process. Please upload a copy of your Driver License or Passport for AML/CTF purposes</p>
												</div>

												<div id="joint_investor_docs" style="display: none;">
													<label>Joint Investor ID DOCS</label>
													<input type="file" name="joint_investor_id_doc" class="form-control" disabled="disabled" required><br>

													<p>Please upload a copy of the joint investors Driver License or Passport for AML/CTF purposes</p>
												</div>
											</div>
										</div>
										<br><br><br>
										<button type="submit" class="btn btn-primary btn-lg" id="checkBtn"><strong>&nbsp;&nbsp;&nbsp;&nbsp;Submit&nbsp;&nbsp;&nbsp;&nbsp;</strong></button><br><br>
									</form>
									@endif
								</dd>
							</dl>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
@stop

@section('js-section')
<script>
	$(document).ready( function() {
		$("input[name='investing_as']").on('change',function() {
			if($(this).is(':checked') && $(this).val() == 'Individual Investor')
			{
				$('#normal_id_docs').removeAttr('style');
				$('#joint_investor_docs').attr('style','display:none;');
				$('#trust_doc').attr('style','display:none;');
				$('#company_trust').attr('style','display:none;');
				$('#joint_investor').attr('style','display:none;');
				$("input[name='joint_investor_first']").attr('disabled','disabled');
				$("input[name='joint_investor_last']").attr('disabled','disabled');
				$("input[name='investing_company_name']").attr('disabled','disabled');
				$("input[name='user_id_doc']").removeAttr('disabled');
				$("input[name='trust_or_company_docs']").attr('disabled','disabled');
				$("input[name='joint_investor_id_doc']").attr('disabled','disabled');
			}
			else if($(this).is(':checked') && $(this).val() == 'Joint Investor')
			{
				$('#joint_investor_docs').removeAttr('style');
				$('#normal_id_docs').removeAttr('style');
				$('#trust_doc').attr('style','display:none;');
				$('#company_trust').attr('style','display:none;');
				$('#joint_investor').removeAttr('style');
				$("input[name='joint_investor_first']").removeAttr('disabled');
				$("input[name='joint_investor_last']").removeAttr('disabled');
				$("input[name='investing_company_name']").attr('disabled','disabled');
				$("input[name='joint_investor_id_doc']").removeAttr('disabled');
				$("input[name='trust_or_company_docs']").attr('disabled','disabled');
				$("input[name='user_id_doc']").removeAttr('disabled');
			}
			else
			{
				$('#trust_doc').removeAttr('style');
				$('#normal_id_docs').attr('style','display:none;');
				$('#joint_investor_docs').attr('style','display:none;');
				$('#joint_investor').attr('style','display:none;');
				$('#company_trust').removeAttr('style');
				$("input[name='joint_investor_first']").attr('disabled','disabled');
				$("input[name='joint_investor_last']").attr('disabled','disabled');
				$("input[name='investing_company_name']").removeAttr('disabled');
				$("input[name='joint_investor_id_doc']").attr('disabled','disabled');
				$("input[name='trust_or_company_docs']").removeAttr('disabled');
				$("input[name='user_id_doc']").attr('disabled','disabled');
			}

		});

		// Slide and show the aml requirements section
		$('.aml-requirements-link').click(function(e){
			$('.aml-requirements-section').slideToggle();
			if($('.aml-requirements-link i').hasClass('fa-plus')){
				$('.aml-requirements-link i').removeClass('fa-plus');
				$('.aml-requirements-link i').addClass('fa-minus');
			}
			else{
				$('.aml-requirements-link i').removeClass('fa-minus');
				$('.aml-requirements-link i').addClass('fa-plus');
			}
		});

		// Submit Request for Form Filling
		$('.send-form-filling-request').click(function(e){
			if (confirm('This will raise a request for form filling. Do you want to continue ?')) {
				console.log('confirmed');
			} else {
				e.preventDefault();
			}
		});
	});
</script>
@endsection
