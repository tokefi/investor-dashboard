@extends('layouts.main')

@section('title-section')
{{$user->first_name}} | @parent
@stop

@section('css-section')
<style type="text/css">
	#userProfileDetails input {
		pointer-events: none;
		background-color: white;
	}
</style>
@stop

{{-- @section('content-section')
<div class="container">
	<br><br>
	<div class="row">
		<div class="col-md-2">
			@include('partials.sidebar', ['user'=>$user, 'active'=>1])
		</div>
		<div class="col-md-10">
			@if (Session::has('message'))
			{!! Session::get('message') !!}
			@endif
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
						<dt>Refer</dt>
						<dd> --}}
							{{-- @forelse(auth()->user()->getReferrals() as $referral)
							<h4>
								{{ $referral->program->name }}
							</h4>
							<code>
								{{ $referral->link }}
							</code>
							<p>
								Number of referred users: {{ $referral->relationships()->count() }}
							</p>
							@empty
							No referrals
							@endforelse
						</dd>
						<dt></dt>
						<dd>{{$user->phone_number}}</dd>
						<hr>
						<dt>Active</dt>
						<dd>@if($user->active) YES @else NO @endif</dd>

						@if($user->gender)
						<dt>Gender</dt>
						<dd>{{$user->gender}}</dd>
						@endif --}}

						{{-- Already Commented --}}
						{{-- @if($user->date_of_birth)
						<dt>Date of Birth</dt>
						<dd><time datetime="{{$user->date_of_birth}}">{{$user->date_of_birth->toFormattedDateString()}}</time></dd>
						@endif --}}
						{{-- end --}}

						{{-- @if($user->active && $user->activated_on)
						<dt>Activated on</dt>
						<dd>
							<time datetime="{{$user->activated_on}}">{{$user->activated_on->toFormattedDateString()}}</time>
							<time datetime="{{$user->activated_on}}">( {{$user->activated_on->diffInDays()}} days ago )</time>
						</dd>
						@endif

						@if($user->last_login)
						<dt>Last Login</dt>
						<dd>
							<time datetime="{{$user->last_login}}">{{$user->last_login->toFormattedDateString()}}</time>
							<time datetime="{{$user->last_login}}">( {{$user->last_login->diffInDays()}} days ago )</time>
						</dd>
						@endif

						<dt>{{$user->roles->count()==1 ? 'Role' : 'Roles'}}</dt>
						<dd>
							@foreach($user->roles as $role)
							<div class="row">
								<div class="col-md-7">
									{{ ucfirst($role->role) }}
								</div>
								<div class="col-md-5">
									@if($user->roles->count()>1)
									@if(strcmp($role->role, "admin"))
									<a href="/users/{{$user->id}}/roles/{{$role->role}}/delete">Delete {{ucfirst($role->role)}} role</a>
									@elseif($user->roles->count() == 2)
									@if ($user->roles->contains('role', 'investor'))
									<a href="/users/{{$user->id}}/roles/developer/add">Add Developer role</a>
									@else
									<a href="/users/{{$user->id}}/roles/investor/add">Add Investor role</a>
									@endif

									@endif
									@else
									<a href="/users/{{$user->id}}/roles/{{ strcmp($role->role, "investor") == 0 ? 'developer' : 'investor'}}/add">Add {{strcmp($role->role, "investor") == 0 ? 'Developer' : 'Investor'}} role</a>
									@endif
								</div>
							</div>
							@endforeach

						</dd> --}}

<!-- 						<dt>ID Verification</dt>
						<dd>
							@if($user->verify_id == '2') Your id docs have been verified <i class="fa fa-check" style="color:green" data-toggle="tooltip" title="Verified User"></i>
							@elseif($user->verify_id == '1') Your id docs have been submitted for verification <i class="fa fa-hourglass-start" style="color:pink" data-toggle="tooltip" title="Submitted"></i>
							@elseif($user->verify_id == '0') You did not submit your id docs for verification <i class="fa fa-clock-o" data-toggle="tooltip" title="Not submitted"></i>
							@elseif($user->verify_id == '-1') Your verification failed please <a href="{{route('users.verification', $user)}}">try again</a> <i class="fa fa-refresh" style="color:red" data-toggle="tooltip" title="Try Again (verification failed)"></i>
							@else <i class="fa fa-clock-o" data-toggle="tooltip" title="Not submitted"></i> @endif
						</dd> -->
						{{-- <dt>Registration Site</dt>
						<dd>
							<a href="{{$user->registration_site}}">{{$user->registration_site}}</a>
						</dd>
					</dl>
				</li>
			</ul>
		</div>
	</div>
</div>
@stop --}}

@section('content-section')
<div class="container">
	<br><br>
	<div class="row">
		<div class="col-md-2">
			@include('partials.sidebar', ['user'=>$user, 'active'=>1])
		</div>
		<div class="col-md-10">
			@if (Session::has('message'))
			{!! Session::get('message') !!}
			@endif
			@if ($errors->any())
			@foreach($errors->all() as $error)
			<div class="alert alert-warning"><strong>Warning!</strong> {{ $error }}</div>
			@endforeach
			@endif
			<ul class="nav nav-tabs" style="margin-top: 0.8em; width: 100%;">
				<li class="active" style="width: 50%;">
					<a data-toggle="tab" href="#profile_tab" style="padding: 0em 2em"><h3 class="text-center">Profile</h3></a>
				</li>
				<li class="" style="width: 50%;">
					<a data-toggle="tab" href="#kyc_tab" style="padding: 0em 2em"><h3 class="text-center">KYC</h3></a>
				</li>
			</ul>
			<div class="tab-content">
				<div id="profile_tab" class="tab-pane fade in active" style="overflow: auto; margin-top: 1em;">
					<ul class="list-group">
						<li class="list-group-item">
							<dl class="dl-horizontal">
								<section id="userProfileDetails">
									<div class="row">
										<div class="col-md-10 col-md-offset-1 form-horizontal">
											<fieldset style="padding: 2rem;">
												<h3 class="text-center" style="font-size: 1.6em; word-spacing: 3px;">YOUR PROFILE DETAILS</h3>
												<div class="row text-right">
													<div class="form-group">
														<div class="col-sm-offset-2 col-sm-9">
															<a href="{{route('users.edit', $user)}}" class="btn btn-warning" data-toggle="tooltip" title="Edit profile and bank account details">Edit Details</a>
														</div>
													</div>
												</div>
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
												<hr>
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
												<br><br>
											</fieldset>
										</div>
									</div>
								</section>
								{{-- <dt></dt>
						<div class="col-md-offset-2 col-xs-offset-3 col-sm-offset-1">
							<dd><h2>{{$user->first_name}} {{$user->last_name}}</h2></dd>
							<dt></dt>
							<dd> --}}

							{{-- <div class="row">
								<div class="col-md-5">
									{{$user->email}}
								</div>
								<div class="col-md-7">
									<a href="{{route('users.edit', $user)}}">edit</a>
								</div>
							</div> --}}
						{{-- </dd>
							<dt></dt> --}}
							{{-- <dd>{{$user->phone_number}}</dd> --}}
					{{-- </div>
					<hr>
					<dt></dt> --}}
					{{-- <dd style="margin-left: 0px;">
						<div class="col-md-10 col-md-offset-1 wow fadeIn text-center @if(!App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->show_powered_by_estatebaron) hide @endif" data-wow-duration="1.5s" data-wow-delay="0.2s">
							<h2 class="text-center wow fadeIn" data-wow-duration="1.5s" data-wow-delay="0.3s" style="font-size:3em;"> Earn KONKRETE tokens
							</h2>
							@forelse(auth()->user()->getReferrals() as $referral)
							@if($referral->program->uri == 'users/create')
							<div class="input-group" style="width: 60%;margin: auto; margin-bottom: 1.1em; margin-top: 1.6em;">
								<input class="form-control text-center" id="foo" value="{{ $referral->link }}"  readonly>
								<span class="input-group-btn">
									<button class="btn btn-default copy" data-clipboard-target="#foo" style="height: 42px;">
										<i class="fa fa-clipboard" aria-hidden="true" alt="Copy to clipboard"></i>
									</button>
								</span>
							</div>
							@endif
							@empty
							No referrals
							@endforelse
							<center>
								<small class="wow fadeIn" data-wow-duration="1.5s" data-wow-delay="0.3s" style="font-size:1.2em;">
									Share this link with your friends and for every user who signs up using this link we will give you @if(App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->referrer_konkrete){{App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->referrer_konkrete}}@else{{App\Helpers\SiteConfigurationHelper::getEbConfigurationAttr()->referrer_konkrete}}@endif and the referred user </br> @if(App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->referee_konkrete){{App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->referee_konkrete}}@else{{App\Helpers\SiteConfigurationHelper::getEbConfigurationAttr()->referee_konkrete}}@endif
									<a href="https://konkrete.io" target="_blank" > KONKRETE </a> cryptotokens each
								</small>
							</center>
						</div>
					</dd> --}}
							</dl>
						</li>
					</ul>
				</div>
				<div id="kyc_tab" class="tab-pane fade " style="margin-top: 1em; overflow: auto;">
					<ul class="list-group">
						<li class="list-group-item">
							<dl class="dl-horizontal">
								<dd><h2>{{$user->first_name}} {{$user->last_name}}</h2></dd>
								<div class="row">
									<div class="col-md-10 col-md-offset-1 well text-center">
										<h4>If you are a resident, citizen or have a valid citizen of Australia, you can complete the KYC using the Digital ID application.</h4>
										<h5>You will see a button below if not verified yet.</h5>
										<br>
										@if($user->digitalIdKyc)
										<h4><span class="text-success"><i class="fa fa-check-circle" aria-hidden="true"></i> Verified By DigitalID</span></h4>
										@else
										<div id="digitalid-verify"></div>
										@endif
									</div>
								</div>
								<dd><h4>Please upload your verified id documents so that we have them on record <br>as part of our KYC AML CTF obligations when you do an investment.</h4></dd><br>
								<dd>
									@if($user->idDoc)
									<h4>You are Investing as
										<b style="color: blue;">{{$user->idDoc->investing_as}}</b>
									</h4><br>
									{{-- <p>{{ Storage::disk('s3')->get($user->idDoc->path) }} test</p> --}}
									{{-- {!!Html::image(('https://s3-ap-southeast-2.amazonaws.com/whitelabel-investors-dashboard/'.$user->idDoc->path),'logo',['width'=>60,'height'=>55])!!} --}}
									<a href="{{$user->idDoc->media_url}}/{{$user->idDoc->path}}" target="_blank">Your Doc 1</a>
									@if ($id2 = $user->idDocs()->where('type', 'Document_2')->first())
									<br />
									<a href="{{$id2->media_url}}/{{$id2->path}}" target="_blank">Your Doc 2</a>
									@endif
									@if($user->idDoc->investing_as == 'Joint Investor')
									<p>Joint Investor Name:<br><b>{{$user->idDoc->joint_first_name}} {{$user->idDoc->joint_last_name}}</b></p>
									<a href="{{$user->idDoc->media_url}}/{{$user->idDoc->joint_id_path}}">Joint Investor Doc</a>
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
									<form class="form-group" action="{{route('users.document.upload',[$user->id])}}" method="POST" enctype="multipart/form-data" rel="form">

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
													<label>Company of Trust Name</label>
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
													<label>ID DOCS 1</label>
													<input type="file" name="user_id_doc" class="form-control" required><br>
													<label>ID DOCS 2</label>
													<input type="file" name="user_id_doc_2" class="form-control" ><br>
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
@endsection
@section('js-section')
<script>
	$(document).ready(function () {
		new Clipboard('.btn');
	});
</script>
<script src="{{ config('services.digitalid_client_url') }}" async defer></script>
	<script>

		// KYC verification functionality
		window.digitalIdAsyncInit = function () {
			digitalId.init({
				clientId : '{{ config('services.digitalid_client_id') }}',
				onComplete: kycVerficationHandler,
				buttonConfig: {
					classNames: [ 'btn-custom-theme', 'kyc-btn' ], // CSS classname(s) as a string or Array
				}
			});

			function kycVerficationHandler(response) {
				console.log(response);
				// Error Action
				if (response.error) {
					console.log(`error: ${response.error}`);
					alert(response.error_description);
					return;
				}

				// Success action: Update DB to set KYC verified
				console.log(`Grant code: ${response.code}`);
				$.ajax({
					url: '{{ route('users.digitalid', [Auth::user()->id]) }}',
					type: 'POST',
					dataType: 'JSON',
					data: {
						'code': response.code,
						'transaction_id': response.transaction_id
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
				}).done(function (data) {
					console.log(data);
					location.reload();
				});
			}
		};


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
					$("input[name='user_id_doc_2']").removeAttr('disabled');
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
					$("input[name='user_id_doc_2']").removeAttr('disabled');
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
					$("input[name='user_id_doc_2']").attr('disabled','disabled');
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
