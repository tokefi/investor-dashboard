<!DOCTYPE html>
<html>
<head>
	<title>Share Certificate</title>
	<style >
		.text-center{
			text-align: center;
		}
	</style>
	{!! Html::style('/css/bootstrap.min.css') !!}
</head>
<body>
	@if($investment->project->master_child )
	<div class="container">
		@if( isset($source))
		<h3 class="text-center">Transaction Table</h3>
		<div class="">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th class="text-center">Projects</th>
						<th class="text-center">Transaction Type</th>
						<th class="text-center">Number of Shares</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="text-center">{{App\InvestmentInvestor::find($investment_id)->project->title}}</td>
						<td class="text-center">BUY</td>
						<td class="text-center"> {{ App\InvestmentInvestor::find($investment_id)->amount }}
						</td>
					</tr>
					@foreach(App\InvestmentInvestor::where('master_investment',$investment_id)->get() as $child)
					<tr>
						<td class="text-center">{{ $child->project->title }}</td>
						<td class="text-center">BUY</td>
						<td class="text-center">{{ $child->amount }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		@endif
		<h3 class="text-center">Allocation Table</h3>
		<div class="">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th class="text-center">Projects</th>
						<th class="text-center">Allocation</th>
					</tr>
				</thead>
				<tbody>
					@foreach($investment->project->children as $child)
					<tr>
						<td class="text-center">{{App\Project::find($child->child)->title}}</td>
						<td class="text-center">{{$child->allocation}} %
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
	@endif
	@if($investment->project->projectspvdetail)
	@if($investment->project->projectspvdetail->certificate_frame)
	<div style="background:url('/assets/images/certificate_frames/{{$investment->project->projectspvdetail->certificate_frame}}');background-position: top center;background-repeat: no-repeat;background-size: 85% 90%;width:100%;height:100%;padding-bottom: 7%;">
		@else
		<div style="padding-top:100px;padding-left:100px;padding-right:100px;margin:-50px;">
			@endif
			@else
			<div style="padding-top:100px;padding-left:100px;padding-right:100px;margin:-50px;">
				@endif
				@if($investment->project->media->where('type', 'spv_logo_image')->first())
				<div class="text-center" style="top:15%;width:100%;position:absolute;z-index:-1;opacity:0.05;"><img src="/{{$investment->project->media->where('type', 'spv_logo_image')->first()->path}}" width="700"></div>
				@endif
				<div class="text-center" style="padding: 10% 20%;">
					<h1>@if($investment->project->share_vs_unit == 1) Redeemable Preference Share @elseif($investment->project->share_vs_unit == 2) Preference Shares @elseif($investment->project->share_vs_unit == 3) Ordinary Shares @else Unit @endif Certificate</h1>
					<br>
					@if($investment->project->media->where('type','spv_logo_image')->first())
					<center><img src="/{{$investment->project->media->where('type','spv_logo_image')->first()->path}}" height="100"></center><br>
					@endif
					@if($investment->project->projectspvdetail)
					{{$investment->project->projectspvdetail->spv_name}}
					@else
					{{'Estate Baron'}}
					@endif
					<br>
					@if($spv=$investment->project->projectspvdetail)
					@if(isset($spv->first()->spv_line_1) && !empty($spv->first()->spv_line_1) && $spv->first()->spv_line_1 != '') {{$spv->spv_line_1}}, @endif @if(isset($spv->first()->spv_line_2) && !empty($spv->first()->spv_line_2) && $spv->first()->spv_line_2 != '') {{$spv->spv_line_2}}, @endif {{$spv->spv_city}}, {{$spv->spv_state}}, {{$spv->spv_postal_code}}
					@endif
					<br>
					@if($investment->project->projectspvdetail)
					{{$investment->project->projectspvdetail->spv_contact_number}}
					@else
					{{'+1 300 033 221'}}
					@endif
					<br><br>
					<p>
						This is to certify {{$investment->user->first_name}} {{$investment->user->last_name}} @if($investment->user->line_1) of {{$investment->user->line_1}}, @if($investment->user->line_2 != '') {{$investment->user->line_2}}, @endif {{$investment->user->city}}, {{$investment->user->state}}, {{$investment->user->postal_code}}@endif owns {{round($investment->shares)}} @if($investment->project->share_vs_unit == 1) redeemable preference shares @elseif($investment->project->share_vs_unit == 2) Preference Shares @elseif($investment->project->share_vs_unit == 3) Ordinary shares @else units @endif of @if($investment->project->projectspvdetail){{$investment->project->projectspvdetail->spv_name}}@else Estate Baron @endif at ${{  $investment->project->share_per_unit_price }} @if($investment->project->share_vs_unit) per share. @else per unit. @endif
					</p>
					<br><br>
					@if($investment->project->media->where('type', 'spv_md_sign_image')->first())
					<img src="/{{$investment->project->media->where('type', 'spv_md_sign_image')->first()->path}}" height="50">
					<br>
					@endif
					@if($investment->project->projectspvdetail)
					{{$investment->project->projectspvdetail->spv_md_name}}
					@else
					{{'Moresh Kokane'}}
					@endif
					<br>
					@if($investment->project->md_vs_trustee)
					{{'Managing Director'}}
					@else
					{{'Trustee'}}
					@endif
					<br>
					@if($investment->project->projectspvdetail)
					{{$investment->project->projectspvdetail->spv_name}}
					@else
					{{'Estate Baron'}}
					@endif
				</div>
			</div>
			@if($investment->project->master_child)
			<h4 class="text-center">Child Investments</h4>
			@foreach($investment->childInvestment as $cInvestment)
			@if($cInvestment->project->projectspvdetail)
			@if($cInvestment->project->projectspvdetail->certificate_frame)
			<div style="background:url('/assets/images/certificate_frames/{{$cInvestment->project->projectspvdetail->certificate_frame}}');background-position: top center;background-repeat: no-repeat;background-size: 85% 90%;width:100%;height:100%;padding-bottom: 7%;">
				@else
				<div style="padding-top:100px;padding-left:100px;padding-right:100px;margin:-50px;">
					@endif
					@else
					<div style="padding-top:100px;padding-left:100px;padding-right:100px;margin:-50px;">
						@endif
						@if($cInvestment->project->media->where('type', 'spv_logo_image')->first())
						<div class="text-center" style="top:15%;width:100%;position:absolute;z-index:-1;opacity:0.05;"><img src="/{{$cInvestment->project->media->where('type', 'spv_logo_image')->first()->path}}" width="700"></div>
						@endif
						<div class="text-center" style="padding: 10% 20%;">
							<h1>@if($cInvestment->project->share_vs_unit == 1) Redeemable Preference Share @elseif($cInvestment->project->share_vs_unit == 2) Preference Shares @elseif($cInvestment->project->share_vs_unit == 3) Ordinary Shares @else Unit @endif Certificate</h1>
							<br>
							@if($cInvestment->project->media->where('type','spv_logo_image')->first())
							<center><img src="/{{$cInvestment->project->media->where('type','spv_logo_image')->first()->path}}" height="100"></center><br>
							@endif
							@if($cInvestment->project->projectspvdetail)
							{{$cInvestment->project->projectspvdetail->spv_name}}
							@else
							{{'Estate Baron'}}
							@endif
							<br>
							@if($spv=$cInvestment->project->projectspvdetail)
							@if(isset($spv->first()->spv_line_1) && !empty($spv->first()->spv_line_1) && $spv->first()->spv_line_1 != '') {{$spv->spv_line_1}}, @endif @if(isset($spv->first()->spv_line_2) && !empty($spv->first()->spv_line_2) && $spv->first()->spv_line_2 != '') {{$spv->spv_line_2}}, @endif {{$spv->spv_city}}, {{$spv->spv_state}}, {{$spv->spv_postal_code}}
							@endif
							<br>
							@if($cInvestment->project->projectspvdetail)
							{{$cInvestment->project->projectspvdetail->spv_contact_number}}
							@else
							{{'+1 300 033 221'}}
							@endif
							<br><br>
							Date: {{ $cInvestment->share_certificate_issued_at->toFormattedDateString()}}
							<br><br>
							<p>
								This is to certify @if($cInvestment->investing_as=='Individual Investor'){{$cInvestment->user->first_name}} {{$cInvestment->user->last_name}}@elseif($cInvestment->investing_as == 'Joint Investor'){{$cInvestment->user->first_name}} {{$cInvestment->user->last_name}} and {{$investing->joint_investor_first_name}} {{$investing->joint_investor_last_name}}@elseif($cInvestment->investing_as=='Trust or Company'){{$investing->investing_company}}@else{{$cInvestment->user->first_name}} {{$cInvestment->user->last_name}}@endif @if($cInvestment->user->line_1) of {{$cInvestment->user->line_1}}, @if($cInvestment->user->line_2 != '') {{$cInvestment->user->line_2}}, @endif {{$cInvestment->user->city}}, {{$cInvestment->user->state}}, {{$cInvestment->user->postal_code}}@endif owns {{ round(app\Helpers\ModelHelper::getTotalInvestmentByUserAndProject($cInvestment->user->id, $cInvestment->project->id)->shares)}} @if($cInvestment->project->share_vs_unit == 1) redeemable preference shares @elseif($cInvestment->project->share_vs_unit == 2) Preference Shares @elseif($cInvestment->project->share_vs_unit == 3) Ordinary shares @else units @endif of @if($cInvestment->project->projectspvdetail){{$cInvestment->project->projectspvdetail->spv_name}}@else Estate Baron @endif at ${{  $cInvestment->project->share_per_unit_price }} @if($investment->project->share_vs_unit) per share. @else per unit. @endif
							</p>
							<br><br>
							@if($cInvestment->project->media->where('type', 'spv_md_sign_image')->first())
							<img src="/{{$cInvestment->project->media->where('type', 'spv_md_sign_image')->first()->path}}" height="50">
							<br>
							@endif
							@if($cInvestment->project->projectspvdetail)
							{{$cInvestment->project->projectspvdetail->spv_md_name}}
							@else
							{{'Moresh Kokane'}}
							@endif
							<br>
							@if($cInvestment->project->md_vs_trustee)
							{{'Managing Director'}}
							@else
							{{'Trustee'}}
							@endif
							<br>
							@if($cInvestment->project->projectspvdetail)
							{{$cInvestment->project->projectspvdetail->spv_name}}
							@else
							{{'Estate Baron'}}
							@endif
						</div>
					</div>
					@endforeach
					
					<br><br><br>
					@endif
				</body>
				</html>
