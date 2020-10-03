	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title>Thank You for expressing interest</title>
		{!! Html::style('/css/app2.css') !!}
		{!! Html::style('/css/bootstrap.min.css') !!}
		<link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:200,300,400,700,200italic,400italic,700italic' rel='stylesheet' type='text/css'>
	</head>
	<body>
		<div class="container">
			<br>
			<section id="section-colors-left" class="color-panel-right panel-open-left center" style="position: static;">
				<div class="color-wrap-left" style="">
					<div class="row">
						<div class="col-md-12 text-center">
							<h2>
								@if($project->projectconfiguration->payment_switch)
								Thank you
								@endif
							</h2>
						</div>
					</div>
					<br>
					@if($project->investment)
					{{-- @if($project->investment->bank) --}}
					<div class="row">
						<div class="col-md-offset-2 col-md-8 text-center">
							<p class="text-center"><strong style="font-size: 1.5em;">Thank you for completing your online Application.</strong></p>
							<p>Your Application has been sent to {{$project->projectspvdetail->spv_name}} for approval.</p>
							<p>Please ensure your Identification documents are provided to fast track your Application.</p>
							<p>You have been sent an email with a copy of your Application. Please check your email for a copy, sent from @if($siteConfiguration->mailSetting){{$siteConfiguration->mailSetting->from}}@endif.</p>
							<p>Please deposit your Application monies of ${{number_format($amount)}} to the Company’s bank account below.</p>
							<br>
							{{-- <h2 class="text-center">
								<div style="margin-top: 1.2rem; text-align: center;">Please deposit ${{number_format($amount)}} to</div>
							</h2> --}}

							@if($project->projectconfiguration->payment_switch)
							@else


							<table class="table table-bordered">
								<tr><td>Bank</td><td>{!!$project->investment->bank!!}</td></tr>
								<tr><td>Account Name</td><td>{!!$project->investment->bank_account_name!!}</td></tr>
								<tr><td>BSB </td><td>{!!$project->investment->bsb!!}</td></tr>
								<tr><td>Account No</td><td>{!!$project->investment->bank_account_number!!}</td></tr>
								<tr><td>SWIFT Code</td><td>{!!$project->investment->swift_code!!}</td></tr>
								<tr><td>Reference</td><td>{!!$project->investment->bank_reference!!}</td></tr>
							</table>

							@if($project->investment->bitcoin_wallet_address)
							<h2 class="text-center" style="font-size: 1.4em; font-weight: 600; margin-bottom: 1.5rem;">Or pay using Bitcoin</h2>
							<table class="table table-bordered">
								<tr><td>Bitcoin wallet address</td><td>{!!$project->investment->bitcoin_wallet_address!!}</td></tr>
							</table>
							@endif
							@endif
							@if($siteConfiguration->show_tokenization)
							<h2 class="text-center" style="font-size: 1.4em; font-weight: 600; margin-bottom: 1.5rem;">Or </h2>
							<p>send ${{number_format($amount)}} {{ $project->payment_token }} {{ $project->payment_contract_address }} to {{ $project->erc20_wallet_address }}. Please ensure to do the transaction only via your recorded wallet address {{ $user->erc20_wallet_address }}.</p>
							@endif
							@if($siteConfiguration->show_tokenization)
							<h2 class="text-center" style="font-size: 1.4em; font-weight: 600; margin-bottom: 1.5rem;">Or </h2>
							<p>send {{ $shares }} {{ $project->erc20_project_token }} {{ $project->erc20_contract_address }} to {{ $project->erc20_wallet_address }}. Please ensure to do the transaction only via your recorded wallet address {{ $user->erc20_wallet_address }}.</p>
							@endif
						</div>
					</div>
					<br>
					{{-- @endif --}}
					@endif
				</div>
			</section>
			<div class="row">
				<div class="col-md-12 text-center">
					{{-- href="javascript:void(0);" onclick="top.window.location. --}}
					<a href='@if($siteConfiguration->project_url) {{$siteConfiguration->project_url}} @else {{route('home')}} @endif'>BACK TO HOME</a>
				</div>
			</div>
		</div>
		{!! Html::script('/js/jquery-1.11.3.min.js') !!}
		{!! Html::script('/js/bootstrap.min.js') !!}
		@if($siteConfig=App\Helpers\SiteConfigurationHelper::getConfigurationAttr()->conversion_pixel)
		{!!$siteConfig!!}
		@endif
	</body>
	</html>
