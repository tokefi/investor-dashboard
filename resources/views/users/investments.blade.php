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
									<th>Agent Name</th>
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
									<td>@if($transaction->user->agent_id) <?php $agent= App\User::find($transaction->user->agent_id); ?> {{ $agent->first_name }} {{ $agent->last_name }} <br> {{ $transaction->user->agent_id }} @else NA @endif </td>
								</tr>
								@endforeach
							</tbody>
						</table>
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
										<th>Redemptions</th>
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
										<td>
											<form action="#" id="redemption_request_form">
												<div class="input-group">
													<input type="number" name="num_shares" min="1" max="{{ $investment->shares }}" step="1" class="form-control" placeholder="Shares" style="min-width: 100px;">
													<div class="input-group-btn">
														<input hidden name="project_id" value="{{$investment->project->id}}" />
														<button class="btn btn-primary form-control" type="submit">Request</button>
													</div>
												</div>
											</form>
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

		$('#redemption_request_form').on('submit', function (e) {
			e.preventDefault();
			if (!confirm('Are you sure you want to submit redemption request?')) {
				return;
			}
			$('.loader-overlay').show();
			let uri = "{{ route('users.investments.requestRedemption') }}";
			let method = 'POST';
			let formdata = new FormData($(this)[0]);
			$.ajax({
                url: uri,
                type: 'POST',
                dataType: 'JSON',
                data: formdata,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: false,
                processData: false
            }).done(function(data){
				$('.loader-overlay').hide();
				if (!data.status) {
					alert(data.message);
					return;
				}
				alert('Redemption Request successfully submitted for ' + data.data.shares + ' shares.');
				location.reload();
			});
		})
	});
</script>
@stop
