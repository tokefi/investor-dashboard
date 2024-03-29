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
					<div style="padding:10em 0;">
						<h1 class="text-center wow fadeIn animated h1-faq first_color">Thank you for submitting your Project <br>
							<small class="first_color">we will review the details and contact you shortly.</small>
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
</script>
<script type="text/javascript">
	var count = 6;
	var redirect = "https://rightsignature.com/forms/MountWaverley-PDS-ee7b99/token/866d9329446";

	function countDown(){
		var timer = document.getElementById("timer");
		if(count > 0){
			count--;
			timer.innerHTML = "<h1><small>This page will redirect in "+count+" seconds.</small></h1>";
			setTimeout("countDown()", 1000);
		}else{
			window.location.href = redirect;
		}
	}
	countDown();
</script>
@stop
