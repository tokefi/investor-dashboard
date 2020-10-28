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
			@if($investmentsWithoutMasterFund->count())
			<div class="row">
				<div class="col-md-12">
					<div class="chart">
						<canvas id="myChart"></canvas>
					</div>
				</div>
				{{-- <div class="col-md-6">
					<div class="chart">
						<select id="input">
						  <option value="VTI">Vanguard Total Stock Market ETF</option>
						  <option value="AAPL">Apple</option>
						  <option value="GOOG">Google</option>
						  <option value="MSFT">Microsoft</option>
						  <option value="BRK.A">Berkshire Hathaway</option>
						  <option value="FB">Facebook</option>
						  <option value="JPM">JPMorgan</option>
						</select>
						<!-- <input id="input" placeholder="Enter a stock"></input> -->
						<button type="button" onclick="displayPrices()">Get Prices</button><br><br>

						<label for="spacing">Duration:</label>
						<div class="slider">
						<datalist id="steplist">
						    <option label="3" value="3">
						    <option label="6" value="6">
						    <option label="9" value="9">
						    <option label="12" value="12">
						    <option label="15" value="15">  
						    <option label="18" value="18">
						    <option label="21" value="21">
						    <option label="24" value="24">
						</datalist>
						<input onchange="displayPrices()" list="steplist" 
						           id="duration" type="range" min="3" max="24" step="3" value="12">  
						</div>
						<canvas id="sharePriceChart"></canvas>
					</div>
				</div>	 --}}			
			</div>
		</div>
		<div class="col-md-12">
			<hr>
			@endif
			{{-- <ul class="list-group">
				<li class="list-group-item">
					<div class="text-center">
						<h3>{{$user->first_name}} {{$user->last_name}}<br><small>{{$user->email}}</small></h3>
					</div>
				</li>
			</ul> --}}
			{{-- <h3 class="text-center">My Investments</h3> --}}
			<ul class="nav nav-tabs" style="width: 100%;">
				<li class="col-md-3 col-xs-12" style="background: none !important;"><a data-toggle="tab" href="#application_tab" style="padding: 0em 2em"><h3 class="text-center">Applications</h3></a></li>
				<li class="active col-md-3 col-xs-12" style="background: none !important;"><a data-toggle="tab" href="#transactions_tab" style="padding: 0em 2em"><h3 class="text-center">Transactions</h3></a></li>
				<li class="col-md-3 col-xs-12" style="background: none !important;"><a data-toggle="tab" href="#positions_tab" style="padding: 0em 2em"><h3 class="text-center">Positions</h3></a></li>
				<li class="col-md-3 col-xs-12" style="background: none !important;"><a data-toggle="tab" href="#Redemption_tab" style="padding: 0em 2em"><h3 class="text-center">Redemptions</h3></a></li>
			</ul>

			<div class="tab-content">
				<div id="application_tab" class="tab-pane fade" style="overflow: auto;">
					<br>
					<div class="table-responsive text-center">
						<table class="table table-bordered table-striped text-center" id="applicationsTable">
							<thead>
								<tr>
									{{-- <th>Investor Name</th> --}}
									<th>Project Name</th>
									<th>Investment Status</th>
									<th>Number of shares</th>
									<th>Price</th>
									<th>Amount</th>
									<th>Agent Name</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								@if($usersInvestments->count())
								@foreach($usersInvestments as $userInvestment)
								<tr>
									<td>{{$userInvestment->project->title}}</td>
									<td class="text-center">@if($userInvestment->accepted && $userInvestment->money_received) Share Certificate Issued @elseif($userInvestment->money_received) Money Received @else Applied @endif</td>
									<td class="text-center">{{round($userInvestment->amount)}}</td>
									<td>${{number_format($userInvestment->buy_rate, 4)}}</td>
									<td>${{number_format(round(($userInvestment->amount)*($userInvestment->buy_rate)), 2)}}</td>
									<td>@if($userInvestment->agent_id) <?php $agent= App\User::find($userInvestment->user->agent_id); ?> {{ $agent->first_name }} {{ $agent->last_name }} <br> {{ $userInvestment->user->agent_id }} @else NA @endif </td>
									<td data-sort="{{date($userInvestment->created_at)}}">{{date('d/m/Y', strtotime($userInvestment->created_at))}}</td>
								</tr>
								@endforeach
								@endif
							</tbody>
						</table>
					</div>
				</div>

				<div id="transactions_tab" class="tab-pane fade in active" style="overflow: auto;">
					<br>
					<div class="table-responsive text-center">
						<table class="table table-bordered table-striped text-center" id="transactionsTable">
							<thead>
								<tr>
									{{-- <th>Investor Name</th> --}}
									<th>Project Name</th>
									{{-- <th>Investment Status</th> --}}
									<th>Transaction Type</th>
									<th>Number of shares</th>
									<th>Price</th>
									<th>Amount</th>
									{{-- <th>Agent Name</th> --}}
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								@if($transactions->count())
								@foreach($transactions as $allTransaction)
								@if($allTransaction->transaction_type)
								<tr>
									<td>{{$allTransaction->project->title}}</td>
									{{-- <td class="text-center">@if(! $allTransaction->accepted && $allTransaction->money_received) Applied @elseif($allTransaction->money_received) Money Received @else Share Certificate Issued @endif</td> --}}
									<td class="text-center">@if($allTransaction->transaction_type == APP\Transaction::BUY || $allTransaction->transaction_type == APP\Transaction::REPURCHASE || $allTransaction->transaction_type == APP\Transaction::CANCELLED){{ $allTransaction->transaction_type }} @else @if($allTransaction->transaction_type == APP\Transaction::DIVIDEND) Dividend {{ round($allTransaction->rate ,2) }} CENTS PER SHARE @elseif($allTransaction->transaction_type == APP\Transaction::FIXED_DIVIDEND)  {{ round($allTransaction->rate ,2) }} % FIXED DIVIDEND @elseif($allTransaction->transaction_type == APP\Transaction::ANNUALIZED_DIVIDEND) {{ $allTransaction->transaction_description }} @endif @endif</td>
									<td class="text-center">{{$allTransaction->number_of_shares}}</td>
									<td>${{number_format($allTransaction->project->share_per_unit_price, 4)}}</td>
									<td>${{number_format($allTransaction->amount, 2)}} </td>
									{{-- <td>NA</td> --}}
									<td data-sort="{{date($allTransaction->transaction_date)}}">{{date('d/m/Y', strtotime($allTransaction->transaction_date))}}</td>
								</tr>
								@else
								<tr>
									<td>{{$allTransaction->project->title}}</td>
									{{-- <td class="text-center">@if($allTransaction->accepted && $allTransaction->money_received) Share Certificate Issued @elseif($allTransaction->money_received) Money Received @else Applied @endif</td> --}}
									<td>NA</td>
									<td class="text-center">{{round($allTransaction->amount)}}</td>
									<td>${{number_format($allTransaction->buy_rate, 4)}}</td>
									<td>${{number_format(round(($allTransaction->amount)*($allTransaction->buy_rate)), 2)}}</td>
									{{-- <td>@if($allTransaction->agent_id) <?php $agent= App\User::find($allTransaction->user->agent_id); ?> {{ $agent->first_name }} {{ $agent->last_name }} <br> {{ $allTransaction->user->agent_id }} @else NA @endif </td> --}}
									<td data-sort="{{date($allTransaction->created_at)}}">{{date('d/m/Y', strtotime($allTransaction->created_at))}}</td>
								</tr>
								@endif
								@endforeach
								@endif
							</tbody>
						</table>
					</div>
				</div>

				<div id="positions_tab" class="tab-pane fade" style="overflow: auto;">
					<br>
					<div>
						<div class="table-responsive text-center">
							<table class="table table-bordered table-striped" id="positionsTable">
								<thead>
									<tr>
										<th>Project Name</th>
										<th>Shares/units</th>
										<th>Price ($)</th>
										<th>Market Value</th>
										<th>Link to share certificate</th>
										<th></th>
										<th>@if($siteConfiguration->show_tokenization)Redemptions/Rollover/Tokenization @else Redemptions/Rollover @endif </th>
									</tr>
								</thead>
								<tbody>
									@if($investments->count())
									@foreach($investments as $investment)
									<tr>
										<td>{{$investment->project->title}}</td>
										<td>{{round($investment->shares)}}</td>
										<td>{{$investment->project->share_per_unit_price}}</td>
										<td>${{number_format($investment->shares * $investment->project->share_per_unit_price)}}</td>
										<td>
											<a href="{{route('user.view.share', [base64_encode($investment->id)])}}" target="_blank">Share Certificate</a>
										</td>
										<td>
											<div class="btn-group project-progress-3way-switch" id="rollover_toggle_{{ $investment->project_id }}" data-toggle="buttons" style="width:200px">
												<label class="btn btn-sm btn-default active" style="padding:5px;">
													<input type="radio" class="rollover-switch" name="rollover_switch_{{$investment->project_id}}" value="encash" data-project-id="{{$investment->project_id}}"> Encash
												</label>
												<label class="btn btn-sm btn-default" style="padding:5px;">
													<input type="radio" class="rollover-switch" name="rollover_switch_{{$investment->project_id}}" value="rollover" data-project-id="{{$investment->project_id}}"> Rollover
												</label>
												@if($siteConfiguration->show_tokenization)
												<label class="btn btn-sm btn-default" style="padding:5px;">
													<input type="radio" class="rollover-switch" name="rollover_switch_{{$investment->project_id}}" value="tokenization" data-project-id="{{$investment->project_id}}"> Tokenization
												</label>
												 @endif
											</div>
										</td>
										<td>
											<div style="" class="redemptions_{{$investment->project_id}}">
												<form action="#" id="redemption_request_form_{{$investment->project_id}}" class="redemption-request-form">
													<div class="input-group">
														<input type="number" name="num_shares" min="1" max="{{ $investment->shares }}" step="1" class="form-control" placeholder="Shares" style="min-width: 85px;" required>
														<div class="input-group-btn">
															<input hidden name="project_id" value="{{$investment->project_id}}" />
															<input hidden name="rollover_action" value="encash" />
															<input hidden name="rollover_project_id" value="" />
															<button class="btn btn-primary form-control" type="submit">Request</button>
														</div>
													</div>
												</form>
											</div>
											<div class="tokenization_{{$investment->project_id}}" style="display: none;">
												<form action="#" id="tokenization_request_form_{{$investment->project_id}}" class="tokenization-request-form">
													<div class="input-group">
														<input type="number" name="num_shares" min="1" max="{{ $investment->shares }}" step="1" class="form-control" placeholder="Shares" style="min-width: 85px;" required>
														<div class="input-group-btn">
															<input hidden name="project_id" value="{{$investment->project_id}}" />
															<input hidden name="rollover_action" value="tokenization" />
															<input hidden name="rollover_project_id" value="" />
															<button class="btn btn-primary form-control" type="submit">Request</button>
														</div>
													</div>
												</form>
											</div>
										</td>
									</tr>
									@endforeach
									@endif
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<div id="Redemption_tab" class="tab-pane fade" style="overflow: auto;">
					<br>
					<div class="table-responsive text-center">
						<table class="table table-bordered table-striped" id="redemptionsTable">
							<thead>
								<tr>
									<th>ID</th>
									<th>Project</th>
									<th>Requested shares</th>
									<th>Price ($)</th>
									<th>Amount</th>
									<th>Requested On</th>
									<th>Last Updated</th>
									<th>Status</th>
									<th>Comments</th>
								</tr>
							</thead>
							<tbody class="text-left">
								@foreach ($redemptions as $redemption)
								<tr style="@if($redemption->status_id != \App\RedemptionStatus::STATUS_PENDING) color: #ccc;  @endif">
									<td>{{ sprintf('%05d', $redemption->id) }}</td>
									<td>
										<a href="/projects/{{ $redemption->project_id }}">{{ $redemption->project->title }}</a><br>
										<address>
											{{$redemption->project->location->line_1}}, {{$redemption->project->location->line_2}}, {{$redemption->project->location->city}}, {{$redemption->project->location->postal_code}}, {{$redemption->project->location->country}}
										</address>
									</td>
									<td class="text-center">{{ $redemption->request_amount }}</td>
									<td class="text-center">
										@if($redemption->status_id == \App\RedemptionStatus::STATUS_PENDING)
										{{ $redemption->project->share_per_unit_price }}
										@else
										{{ $redemption->price }}
										@endif
									</td>
									<td class="text-center">
										@if($redemption->status_id != \App\RedemptionStatus::STATUS_PENDING)
										${{ number_format(round($redemption->request_amount * $redemption->price, 2)) }}
										@else
										${{ number_format(round($redemption->request_amount * $redemption->project->share_per_unit_price, 2)) }}
										@endif
									</td>
									<td>{{ $redemption->created_at->toFormattedDateString() }}</td>
									<td>{{ $redemption->updated_at->diffForHumans() }}</td>
									<td>{{ $redemption->type }}
										<br>
										{{ $redemption->status->name }}
										<br>
										@if($redemption->status_id == \App\RedemptionStatus::STATUS_PARTIAL_ACCEPTANCE)
										<span class="badge"><strong>Accepted:</strong> {{ $redemption->accepted_amount }}/{{ $redemption->request_amount }}</span>
										@endif
									</td>
									<td>{{ $redemption->comments }}</td>
								</tr>    
								@endforeach
								@foreach ($tokenizations as $redemption)
								<tr style="@if($redemption->status_id != \App\RedemptionStatus::STATUS_PENDING) color: #ccc;  @endif">
									<td>{{ sprintf('%05d', $redemption->id) }}</td>
									<td>
										<a href="/projects/{{ $redemption->project_id }}">{{ $redemption->project->title }}</a><br>
										<address>
											{{$redemption->project->location->line_1}}, {{$redemption->project->location->line_2}}, {{$redemption->project->location->city}}, {{$redemption->project->location->postal_code}}, {{$redemption->project->location->country}}
										</address>
									</td>
									<td class="text-center">{{ $redemption->request_amount }}</td>
									<td class="text-center">
										@if($redemption->status_id == \App\RedemptionStatus::STATUS_PENDING)
										{{ $redemption->project->share_per_unit_price }}
										@else
										{{ $redemption->price }}
										@endif
									</td>
									<td class="text-center">
										@if($redemption->status_id != \App\RedemptionStatus::STATUS_PENDING)
										${{ number_format(round($redemption->request_amount * $redemption->price, 2)) }}
										@else
										${{ number_format(round($redemption->request_amount * $redemption->project->share_per_unit_price, 2)) }}
										@endif
									</td>
									<td>{{ $redemption->created_at->toFormattedDateString() }}</td>
									<td>{{ $redemption->updated_at->diffForHumans() }}</td>
									<td>{{ $redemption->type }}
										<br>
										{{ $redemption->status->name }}
										<br>
										@if($redemption->status_id == \App\RedemptionStatus::STATUS_PARTIAL_ACCEPTANCE)
										<span class="badge"><strong>Accepted:</strong> {{ $redemption->accepted_amount }}/{{ $redemption->request_amount }}</span>
										@endif
									</td>
									<td>{{ $redemption->comments }}</td>
								</tr>    
								@endforeach
							</tbody>
						</table>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

<!-- Redemption reject Modal -->
<div id="rollover_project_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Select rollover project</h4>
			</div>
			<form action="#" id="rollover_project_form" name="rollover_project_form" data-project-id="">
				<div class="modal-body" style="padding: 30px;">
					<small class="muted">** Select the project for which you want to rollover the shares. Once you confirm, submit the redemption request and you will be redirected to sign the application form of rollover project.</small><br><br>
					<div class="form-group">
						<label for="rollover_project">Select project for rollover: </label>
						<select name="rollover_project" id="rollover_project" class="form-control">
							@foreach ($projects as $project)
							<option value="{{ $project->id }}">
								<strong>{{ $project->title }} - </strong>
								<small class="text-grey">( {{$project->location->line_1}}, @if($project->location->line_2){{$project->location->line_2}},@endif {{$project->location->city}}, {{$project->location->postal_code}}, {{$project->location->country}} )</small>
							</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger" >Confirm</button>
				</div>
			</form>
		</div>
	</div>
</div>
@stop

@section('js-section')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
{{-- <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script> --}}
{{-- <script src="https://www.chartjs.org/dist/master/Chart.min.js"></script> --}}
{!! Html::script('/js/Chart.min.js') !!}
<script src="https://cdnjs.cloudflare.com/ajax/libs/google-palette/1.1.0/palette.min.js"></script>
<script src="https://d3js.org/d3.v5.min.js"></script>

{{-- <script type="text/javascript">
	displayPrices();
function displayPrices(){
  
let duration = document.getElementById("duration").value;
let name = document.getElementById("input").value;
var xmlhttp = new XMLHttpRequest(),
    url = 'https://www.alphavantage.co/query?function=TIME_SERIES_MONTHLY&symbol=' + name + '&apikey=PVY8MCGPGIBP69X4';

xmlhttp.open('GET', url, true);
xmlhttp.onload = function() {
  // const dates = lastWeek();
  if (this.readyState == 4 && this.status == 200) {
    json=JSON.parse(this.responseText);
    // document.getElementById('stock').innerHTML =
    //   json['Meta Data']['2. Symbol'];
    // get an array of object keys
    let keys = Object.keys(json['Monthly Time Series']);
    var dates = [];
    var pricesClose = [];
    const months = [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", 
           "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ];
    // get prices for last no. of months
    for (let i=0; i<duration; i++) {
      let key = keys[i];
      // console.log('key', key);
      pricesClose.push(json['Monthly Time Series'][key]['4. close']);
      dates.push(months[Number(key.slice(5, 7) - 1)] + key.slice(2, 4));
    }
    // console.log(dates);
    // console.log(pricesClose);
    displayChart(name, dates, pricesClose)
  }
};
xmlhttp.send();
}

function displayChart(name, dates, pricesClose) {
  let labels = dates.reverse();
  let data = pricesClose.reverse();
  let ctx = document.getElementById('sharePriceChart').getContext('2d');
  let chart = new Chart(ctx, {
      // The type of chart we want to create
      type: 'line',

      // The data for our dataset
      data: {
          labels: labels,
          datasets: [{
              label: name,
              borderColor: 'rgb(255, 99, 132)',
              data: data,
            lineTension: 0,
          }]
      },

      // Configuration options go here
      options: {}
  });
}
</script> --}}
<script type="text/javascript">
	$(document).ready(function(){
		var transactionsTable = $('#transactionsTable').DataTable({
			"order": [],
			"iDisplayLength": 50,
			"language": {
				"search": "",
				"searchPlaceholder": "Search",
			}
		});
		var positionsTable = $('#positionsTable').DataTable({
			"order": [],
			"iDisplayLength": 50,
			"language": {
				"search": "",
				"searchPlaceholder": "Search",
			}
		});
		var applicationsTable = $('#applicationsTable').DataTable({
			"order": [],
			"iDisplayLength": 50,
			"language": {
				"search": "",
				"searchPlaceholder": "Search",
			}
		});
		var redemptionsTable = $('#redemptionsTable').DataTable({
			"order": [],
			"iDisplayLength": 50,
			"language": {
				"search": "",
				"searchPlaceholder": "Search",
			}
		});

		$('.rollover-switch').on('change', function (e) {
			let action = $(this).val();
			let projectId = $(this).attr('data-project-id');
			if (action == 'rollover') {
				// show modal for rollover project selection
				$('.redemptions_'+projectId).attr('style','');
				$('.tokenization_'+projectId).attr('style','display:none;');
				$('#rollover_project_modal #rollover_project_form').attr('data-project-id', projectId);
				$('#rollover_project_modal').modal({
					keyboard: false,
					backdrop: 'static'
				});	
			} else if(action === 'tokenization'){
				$('.redemptions_'+projectId).attr('style','display:none;');
				$('.tokenization_'+projectId).attr('style','');
				if (!confirm('Are you sure you want to submit tokenization request?')) {
				return;
			}
			}
			else {
				$('#redemption_request_form_' + projectId + ' input[name=rollover_project_id]').val('');
			}
			$('#redemption_request_form_' + projectId + ' input[name=rollover_action]').val(action);
		});

		$('#rollover_project_modal #rollover_project_form').on('submit', function (e) {
			e.preventDefault();
			let rolloverProjectId = $(this).find('#rollover_project').val();
			let projectId = $(this).attr('data-project-id');
			$('#redemption_request_form_' + projectId + ' input[name=rollover_project_id]').val(rolloverProjectId);
			$('#rollover_project_modal').modal('toggle');
		});

		$('.redemption-request-form').on('submit', function (e) {
			e.preventDefault();
			if (!confirm('Are you sure you want to submit redemptions request?')) {
				return;
			}
			$('.loader-overlay').show();
			let form = $(this);
			let projectId = form.find('input[name=project_id]').val();
			let rolloverAction = form.find('input[name=rollover_action]').val();
			let uri = "{{ route('users.investments.requestRedemption') }}";
			let method = 'POST';
			let formdata = new FormData(form[0]);
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

				if (rolloverAction == 'rollover') {
					let rolloverProjectId = form.find('input[name=rollover_project_id]').val();
					alert('Redemption Request successfully submitted for ' + data.data.shares + ' shares. After this you will be redirected to rollover project application, please sign and submit it to apply rollover.');
					window.location = data.data.rollover_url;
					return;
				}

				alert('Redemption Request successfully submitted for ' + data.data.shares + ' shares.');
				location.reload();
			});
		})

		$('.tokenization-request-form').on('submit', function (e) {
			e.preventDefault();
			$('.loader-overlay').show();
			let form = $(this);
			let projectId = form.find('input[name=project_id]').val();
			let rolloverAction = form.find('input[name=rollover_action]').val();
			let uri = "{{ route('users.investments.requestTokenization') }}";
			let method = 'POST';
			let formdata = new FormData(form[0]);
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
				alert('Tokenization Request successfully submitted for ' + data.data.shares + ' shares.');
				location.reload();
			});
		})


		@if($investmentsWithoutMasterFund->count())
		var marketValue = [];
		var projectName = [];
		@foreach($investmentsWithoutMasterFund as $investment )
		marketValue.push(Math.round({{$investment->shares * $investment->project->share_per_unit_price}}));
		projectName.push('{{$investment->project->title}}');
		@endforeach

		var sumOfMarketValues = marketValue.reduce(function(a, b){
			return a + b;
		}, 0);

	    // Create our number formatter.
	    var formatter = new Intl.NumberFormat('en-US', {
	    	style: 'currency',
	    	currency: 'USD',
	    });
	    var pieChartTitle = 'Total Market Value - ' + formatter.format(sumOfMarketValues);

	    var ctx = document.getElementById('myChart').getContext('2d');
	    var pieColors = [
	    '#08519c',
	    '#2171b5',
	    '#4292c6',
	    '#6baed6',
	    '#9ecae1',
	    '#a6cee6',
	    '#c6dbef',
	    '#c4dfef',
	    '#f7fbff',
	    '#e1eff7'
	    ];

	    var chart = new Chart(ctx, {
	    	type: 'pie',
	    	data: {
	    		labels: projectName,
	    		datasets: [{
	    			backgroundColor: pieColors,
	    			hoverBorderWidth: 8,
	    			backgroundColor: pieColors,
	    			hoverBackgroundColor: pieColors,
	    			hoverBorderColor: pieColors,
	    			borderColor: '#eee',
	    			data: marketValue,
	    		}]
	    	},
	    	options: {
	    		animation: false,
	    		responsive: true,
	    		layout: {
	    			padding: {
	    				left: 0,
	    				right: 0,
	    				top: 10,
	    				bottom: 0
	    			}			    
	    		},
	    		legend: {
	    			display: true,
	    			position: 'right',
	    			align: 'middle'
	    		},	    
	    		title: {
	    			display: true,
	    			text: pieChartTitle,
	    			fontSize: 14,
	    			padding: {
	    				bottom: 20,
	    			}
	    		},
	    		tooltips: {
	    			callbacks: {
	    				title: function (tooltipItem, data) { return data.labels[tooltipItem[0].index]; },
	    				label: function (tooltipItem, data) {
	    					var amount = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
	    					var total = eval(data.datasets[tooltipItem.datasetIndex].data.join("+"));
                            // return '$' + amount + ' / ' + '$' + total + ' ( ' + parseFloat(amount * 100 / total).toFixed(2) + '% )';
                            return '$' + amount + ' ( ' + parseFloat(amount * 100 / total).toFixed(2) + '% )';
                        },
                    }
                },
            }
        });




	    @endif

	});
</script>
@stop
