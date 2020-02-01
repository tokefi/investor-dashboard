@extends('layouts.main')

@section('title-section')
Thank You | @parent
@stop

@section('css-section')
{!! Html::style('plugins/animate.css') !!}
@stop

@section('content-section')
<div class="container">
	<div class="row">
		<div class="col-md-offset-2 col-md-8">
			<div class="row" id="section-1">
				<div class="col-md-12">
					@if (Session::has('message'))
					<div class="alert alert-success text-center">{{ Session::get('message') }}</div>
					@endif
					@if (count($errors) > 0)
					<div class="alert alert-danger">
						<ul>
							@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
					@endif
					<div style="padding:10em 0;">
						<h1 class="text-center wow fadeIn animated h1-faq">To Continue your @if($type == 'eoi')Expression of Interest @else Application @endif <br><small>We have sent a 6 digit code to your email address. Please enter that over here to confirm your email id and complete the registration process.</small><br><br><br>
							<small>
								<form action="{{route('users.registration.code')}}" method="POST" id="eoiFormToken">
									{{csrf_field()}}
									<div class="row">
										<div class="col-md-6 col-md-offset-3">
											<div class="form-group ">
												<input type="text" name="eoiCode" required="required" class="form-control" id="eoiCode">
											</div>
										</div>
									</div>
									<input type="hidden" name="first_name" value="{{ $userData['fn'] }}">
									<input type="hidden" name="last_name" value="{{ $userData['ln'] }}">
									<button type="submit" class="btn btn-lg btn-danger font-semibold text-right second_color_btn eoiFormSubmitBtn">Confirm</button>
								</form>
							</small>
						</h1>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
@stop

@section('js-section')
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.0/jquery.scrollTo.min.js"></script>
{!! Html::script('plugins/wow.min.js') !!}
<script type="text/javascript">
	new WOW().init({
		boxClass:     'wow',
		animateClass: 'animated',
		mobile:       true,
		live:         true
	});
	$(function() {
		$('#eoiCode').on('keypress', function(e) {
			if (e.which == 32)
				return false;
		});
		$('#eoiFormToken').submit(function (e) {
			$('.loader-overlay').show();
			return true;
		});
	});
	$(function(){
		$('#eoiCode').bind('input', function(){
			$(this).val(function(_, v){
				return v.replace(/\s+/g, '');
			});
		});
	});
</script>
@stop
