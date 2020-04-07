@extends('layouts.main')

@section('title-section')
{{$user->first_name}} Investments | @parent
@stop

@section('css-section')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css">
<style type="text/css">
	#investmentsTable th {
		text-align: center;
	}
</style>
@stop

@section('content-section')
<div class="container">
	<br>
	<div class="row">
		<div class="col-md-2">
			@include('partials.sidebar', ['active'=>6])
		</div>
		<div class="col-md-10">
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
			<h3 class="text-center">My Investments</h3>
			<ul class="nav nav-tabs" style="margin-top: 2em; width: 100%;">
				<li class="active" style="width: 50%;"><a data-toggle="tab" href="#investors_tab" style="padding: 0em 2em"><h3 class="text-center">Transactions</h3></a></li>
				<li style="width: 50%;"><a data-toggle="tab" href="#eoi_tab" style="padding: 0em 2em"><h3 class="text-center">Positions</h3></a></li>
			</ul>

			<div class="tab-content">
				<div id="investors_tab" class="tab-pane fade in active" style="overflow: auto;">
					<br><br>
					<div class="table-responsive text-center">
								<table class="table table-bordered table-striped text-center" id="transactionTable">
								<thead>
									<tr>
										<th>Investor Name</th>
										<th>Project Name</th>
										<th>Transaction type</th>
										<th>Date</th>
										<th>Amount</th>
										<th>Rate</th>
										<th>Number of shares</th>
									</tr>
								</thead>
								<tbody>
									@foreach($transactions as $transaction)
									<tr>
										<td>{{$transaction->user->first_name}} {{$transaction->user->last_name}}</td>
										<td>@if($transaction->project->projectspvdetail){{$transaction->project->title}}@endif</td>
										<td class="text-center">@if($transaction->transaction_type == "DIVIDEND") {{"ANNUALIZED DIVIDEND"}} @else {{$transaction->transaction_type}} @endif</td>
										<td>{{date('m-d-Y', strtotime($transaction->transaction_date))}}</td>
										<td>${{$transaction->amount}}</td>
										<td>{{$transaction->rate}}</td>
										<td>{{$transaction->number_of_shares}}</td>
									</tr>
									@endforeach
								</tbody>
							</table>
								{{-- <thead>
									<tr>
										<th>Project Name</th>
										<th>Investment Amount</th>
										<th>Investment Date</th>
										<th>Investment status</th>
										<th>Link to share certificate</th>
										<th>Link to application form</th>
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
											<td><a href="{{route('user.view.application', [base64_encode($investment->id)])}}" target="_blank">Application form</a></td>
											<td></td>
											<td></td>
										</tr>
									@endforeach
									@endif
								</tbody> --}}
					</div>
				</div>

				<div id="eoi_tab" class="tab-pane fade" style="margin-top: 2em;overflow: auto;">
					<div>
						<div class="table-responsive text-center">
							<table class="table table-bordered table-striped" id="positionsTable">
								<thead>
									<tr>
										<th>Project Name</th>
										<th>Project Address</th>
										<th>Shares/units</th>
										<th>Price ($)</th>
										<th>Market Value</th>
										<th>Link to share certificate</th>
									</tr>
								</thead>
								<tbody>
									@if($investments->count())
									@foreach($investments as $investment)
									<tr>
										<td>{{$investment->project->title}}</td>
										<td>
											{{$investment->project->location->line_1}}, 
											{{$investment->project->location->line_2}}, 
											{{$investment->project->location->city}}, 
											{{$investment->project->location->postal_code}},
											{{$investment->project->location->country}}
										</td>
										<td>{{$investment->shares}}</td>
										<td>{{$investment->project->share_per_unit_price}}</td>
										<td>${{number_format($investment->shares * $investment->project->share_per_unit_price)}}</td>
										<td>
											<a href="{{route('user.view.share', [base64_encode($investment->id)])}}" target="_blank">Share Certificate</a>
										</td>
									</tr>
									@endforeach
									@endif
								</tbody>
							</table>
						</div>
					</div>
				</div>
				
			</div>

			{{-- <ul class="list-group">
				@if($user->investments->count())
				@foreach($user->investments as $project)
				<a href="" class="list-group-item">{{$project->title}}</a>
				@endforeach
				@else
				<li class="list-group-item text-center alert alert-warning">Not Shown any Interest</li>
				@endif
			</ul> --}}
		</div>
	</div>
</div>
@stop

@section('js-section')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		var transactionsTable = $('#transactionsTable').DataTable({
			"order": [[5, 'desc'], [0, 'desc']],
			"iDisplayLength": 50
		});
		var positionsTable = $('#positionsTable').DataTable({
			"order": [[5, 'desc'], [0, 'desc']],
			"iDisplayLength": 50
		});
	});
</script>
@stop
