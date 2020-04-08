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
								Confirmation request has been sent to {{ $clientApplication->client_first_name }} {{ $clientApplication->client_last_name }} at {{ $clientApplication->client_email }}.
							</h2>
						</div>
					</div>
				</div>
			</section>
			<div class="row">
				<div class="col-md-12 text-center">
					<a href="javascript:void(0);" onclick="top.window.location.href='{{route('home')}}';">BACK TO HOME</a>
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
