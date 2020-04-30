@extends('layouts.main')
@section('title-section')
{{$project->title}} | Dashboard | @parent
@stop

@section('meta')
<meta name="csrf-token" content="{{ csrf_token() }}" />
@stop

@section('css-section')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<style type="text/css">
	.dividend-confirm-table td, .repurchase-confirm-table td, .investor-statement-confirm-table td{ padding: 10px 20px; }
	.dividend-confirm-table, .repurchase-confirm-table, .investor-statement-confirm-table{ margin-left:auto;margin-right:auto; }
	.success-icon {
		border: 1px solid;
		padding: 2px;
		border-radius: 20px;
		color: green;
		margin-left: 0.8rem;
	}
	.issue-share-certi-btn, .money-received-btn {
		word-wrap: break-word !important;
		white-space: pre-wrap !important;
	}
	table {
		font-size: 14px;
		word-break: break-word !important;
	}

	h3 {
		font-size: 23px;
	}

	table th, table td {
		word-break: break-word;
		text-align: center;
	}

	table.dataTable thead th, table.dataTable thead td {
		padding: 10px 12px;
	}

	table.dataTable tbody th, table.dataTable tbody td {
		padding: 8px 9px;
	}

	@media (min-width: 768px) {
		.share-registry-table {
			word-break: break-all;
		}
		.investors-table {
			table-layout: fixed;
			word-wrap: break-word;
		}
	}

	td {
		text-align: center;
	}
</style>
@stop

@section('content-section')
<div class="container-fluid">
	<br>
	<div class="row">
		{{--<div class="col-md-2">
			@include('dashboard.includes.sidebar', ['active'=>3])
		</div>--}}
		<div class="col-md-12">
			<div class="row">
				<div class="col-md-12">
					@if (Session::has('message'))
					{!! Session::get('message') !!}
					@endif
					<h2 class="text-center" style="margin-top: 0.9rem;"><a href='{{ url() }}/dashboard/projects/{{ $project->id }}/edit'>{{$project->title}}</a>
						{{-- <address class="text-center">
							<small>{{$project->location->line_1}}, {{$project->location->line_2}}, {{$project->location->city}}, {{$project->location->postal_code}},{{$project->location->country}}
							</small>
						</address> --}}
					</h2>
					<form id="update_share_price_form" action="{{route('dashboard.projects.updateSharePrice', [$project->id])}}" method="POST" class="pull-right">
						{{csrf_field()}}
						<label for="#update_share_price">Share Price:   $</label>
						<input type="number" name="update_share_price" id="update_share_price" step="0.0001" value="{{ $project->share_per_unit_price }}" required="required"> <input type="submit" class="btn btn-warning" value="UPDATE">
					</form>
				</div>
			</div>
			<ul class="nav nav-tabs" style="margin-top: 0.8em; width: 100%;">
				<li class="active" style="width: 19%;"><a data-toggle="tab" href="#investors_tab" style="padding: 0em 2em"><h3 class="text-center">Applications</h3></a></li>
				<li style="width: 26%;"><a data-toggle="tab" href="#share_registry_tab" style="padding: 0em 2em"><h3 class="text-center">Accepted applications</h3></a></li>
				<li style="width: 19%;"><a data-toggle="tab" href="#new_registry" style="padding: 0em 2em"><h3 class="text-center">Registry</h3></a></li>
				{{-- <li style="width: 20%;"><a data-toggle="tab" href="#transactions_tab" style="padding: 0em 2em"><h3 class="text-center">Transactions</h3></a></li> --}}
				{{-- <li style="width: 30%;"><a data-toggle="tab" href="#positions_tab" style="padding: 0em 2em"><h3 class="text-center">Position records</h3></a></li> --}}
				<li style="width: 18%;"><a data-toggle="tab" href="#expression_of_interest_tab" style="padding: 0em 2em"><h3 class="text-center">Project EOI</h3></a></li>
				<li style="width: 18%;"><a data-toggle="tab" href="#eoi_tab" style="padding: 0em 2em"><h3 class="text-center">Upcoming</h3></a></li>
			</ul>
			<div class="tab-content">
				<div id="investors_tab" class="tab-pane fade in active" style="overflow: auto;">
					<style type="text/css">
						.edit-input{
							display: none;
						}
					</style>
					<br>
					<table class="table table-bordered table-striped investors-table" id="investorsTable" style="margin-top: 1em;">
						<thead>
							<tr>
								<th>Unique ID</th>
								<th>Investors Details</th>
								<th>Investment Date</th>
								<th>Number of Shares</th>
								<th>Share Price ($)</th>
								<th>Amount</th>
								<th>Is Money Received</th>
								<th>Issue @if($project->share_vs_unit) Share @else Unit @endif Certificate</th>
								<th>Investor Document</th>
								<th>Joint Investor</th>
								<th>Entity Details</th>
								@if(!$project->retail_vs_wholesale)<th>Wholesale Investment</th>@endif
								<th>Application Form</th>
								<th>Interested to Buy</th>
								<th>Agent name</th>
							</tr>
						</thead>
						<tbody>
							@foreach($investments as $investment)
							@if(!$investment->hide_investment)
							<tr id="application{{$investment->id}}">
								<td data-sort="{{$investment->id}}">INV{{$investment->id}}
									<a href="{{route('dashboard.application.view', [$investment->id])}}" class="edit-application" style="margin-top: 1.2em;"><br>
										<i class="fa fa-edit" aria-hidden="true"></i>
									</a>
									@if(!$investment->money_received && !$investment->accepted)
												{{-- <form action="{{route('dashboard.investment.hideInvestment', $investment->id)}}" method="POST">
												{{method_field('PATCH')}}
												{{csrf_field()}} --}}
												{{-- <a class="send-app-form-link" href="javascript:void(0);" data="{{$projectsEoi->id}}" onclick="sendEOIAppFormLink()"><b>Resend link</b></a> --}}
												<a href="javascript:void(0);" class="hide-investment" data="{{$investment->id}}"><br>
													<i class="fa fa-trash" aria-hidden="true"></i>
												</a>
												@endif
												<td>
													<div class="text-left">
														<a href="{{route('dashboard.users.show', [$investment->user_id])}}" >
															<b>{{$investment->user->first_name}} {{$investment->user->last_name}}</b>
														</a>
														<br>{{$investment->user->email}}<br>{{$investment->user->phone_number}}
													</div>
												</td>
												<td data-sort="{{$investment->created_at->toFormattedDateString()}}">
													<div class="text-center">{{$investment->created_at->toFormattedDateString()}}</div>
												</td>
												<td class="text-center">
													<div class="">
														<form action="{{route('dashboard.investment.update', [$investment->id])}}" method="POST">
															{{method_field('PATCH')}}
															{{csrf_field()}}
															<a href="#edit" class="edit">{{ round($investment->amount) }}</a>

															<input type="text" class="edit-input form-control" name="amount" id="amount" value="{{$investment->amount}}" style="width: 100px;">
															<input type="hidden" name="investor" value="{{$investment->user->id}}">
														</form>
													</div>
												</td>
												<td class="text-center">
													{{ number_format($investment->buy_rate, 4) }}
												</td>
												<td class="text-center">
													${{ number_format(round($investment->amount * $investment->buy_rate, 2)) }}
												</td>
												<td>
													<div class="text-center">
														<form action="{{route('dashboard.investment.moneyReceived', $investment->id)}}" method="POST">
															{{method_field('PATCH')}}
															{{csrf_field()}}
															@if($investment->money_received || $investment->accepted)
															<i class="fa fa-check" aria-hidden="true" style="color: #6db980;">&nbsp;<br><small style=" font-family: SourceSansPro-Regular;">Money Received</small></i>
															@else
															{{-- <input type="submit" name="money_received" class="btn btn-primary money-received-btn" value="Money Received"> --}}
															<div class="pretty p-svg ">
																<input type="checkbox" name="money_received" value="Money Received" class="money-received-btn" data-toggle="tooltip" title="Money received" @if(SiteConfigurationHelper::isSiteAgent()) disabled @endif @if($investment->master_investment) disabled @endif>
																<div class="state p-success">
																	<!-- svg path --> 
																	<svg class="svg svg-icon" viewBox="0 0 20 20">
																		<path d="M7.629,14.566c0.125,0.125,0.291,0.188,0.456,0.188c0.164,0,0.329-0.062,0.456-0.188l8.219-8.221c0.252-0.252,0.252-0.659,0-0.911c-0.252-0.252-0.659-0.252-0.911,0l-7.764,7.763L4.152,9.267c-0.252-0.251-0.66-0.251-0.911,0c-0.252,0.252-0.252,0.66,0,0.911L7.629,14.566z" style="stroke: white;fill:white;">
																			
																		</path>
																	</svg>
																 <label></label>
																</div>
															</div>
															@endif
														</form>
													</div>
												</td>
												<td>
													<div class="text-center">
														<form action="{{route('dashboard.investment.accept', $investment->id)}}" method="POST">
															{{method_field('PATCH')}}
															{{csrf_field()}}

															{{-- <input type="checkbox" name="accepted" onChange="this.form.submit()" value={{$investment->accepted ? 0 : 1}} {{$investment->accepted ? 'checked' : '' }}> Money {{$investment->accepted ? 'Received' : 'Not Received' }} --}}
															@if($investment->accepted)
															<i class="fa fa-check" aria-hidden="true" style="color: #6db980;">&nbsp;<br><small style=" font-family: SourceSansPro-Regular;">@if($project->share_vs_unit) Share @else Unit @endif certificate issued</small></i>
															@else
															{{-- <input type="submit" name="accepted" class="btn btn-primary issue-share-certi-btn" value="Issue @if($project->share_vs_unit) share @else unit @endif certificate"> --}}
															<div class="pretty p-svg">
																<input type="checkbox" name="accepted" value="issue @if($project->share_vs_unit) share @else unit @endif certificate" @if(SiteConfigurationHelper::isSiteAgent()) disabled @endif @if($investment->master_investment) disabled @endif class="issue-share-certi-btn" data-toggle="tooltip" title="Issue share certificate">
																<div class="state p-success">
																	<!-- svg path --> 
																	<svg class="svg svg-icon" viewBox="0 0 20 20">
																		<path d="M7.629,14.566c0.125,0.125,0.291,0.188,0.456,0.188c0.164,0,0.329-0.062,0.456-0.188l8.219-8.221c0.252-0.252,0.252-0.659,0-0.911c-0.252-0.252-0.659-0.252-0.911,0l-7.764,7.763L4.152,9.267c-0.252-0.251-0.66-0.251-0.911,0c-0.252,0.252-0.252,0.66,0,0.911L7.629,14.566z" style="stroke: white;fill:white;">
																			
																		</path>
																	</svg>
																 <label></label>
																</div>
															</div>
															@endif
															<input type="hidden" name="investor" value="{{$investment->user->id}}">
														</form>
													</div>
												</td>
												<td>
													@if($investment->userInvestmentDoc->where('type','normal_name')->last())
													<a href="/{{$investment->userInvestmentDoc->where('type','normal_name')->last()->path}}" target="_blank">{{$investment->user->first_name}} {{$investment->user->last_name}} Doc</a>
													<a href="#" class="pop">
														<img src="/{{$investment->userInvestmentDoc->where('type','normal_name')->last()->path}}" style="width: 120px; margin: auto !important;;" class="img-responsive">
													</a>
													<div class="modal fade" id="imagemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
														<div class="modal-dialog">
															<div class="modal-content">
																<div class="modal-body">
																	<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
																	<img src="" class="imagepreview" style="width: 100%;" >
																</div>
															</div>
														</div>
													</div>
													<script>
														$(function() {
															$('.pop').on('click', function() {
																$('.imagepreview').attr('src', $(this).find('img').attr('src'));
																$('#imagemodal').modal('show');
															});
														});
													</script>
													@else
													NA
													@endif
												</td>
												<td>
													@if($investment->userInvestmentDoc)
													@if($investment->userInvestmentDoc->where('type','joint_investor')->last())
													<a href="/{{$investment->userInvestmentDoc->where('type','joint_investor')->last()->path}}" target="_blank">{{$investment->investingJoint->joint_investor_first_name}} {{$investment->investingJoint->joint_investor_last_name}} Doc</a>
													<br>
													@else
													NA
													@endif
													@endif
												</td>
												<td>
													@if($investment->userInvestmentDoc)
													@if($investment->userInvestmentDoc->where('type','trust_or_company')->last())
													<a href="/{{$investment->userInvestmentDoc->where('type','trust_or_company')->last()->path}}" target="_blank">
														{{$investment->investingJoint->investing_company}} Doc
													</a>
													@else
													NA
													@endif
													@else
													NA
													@endif
												</td>
												@if(!$project->retail_vs_wholesale)
												<td>@if($investment->wholesaleInvestment)<a href="#" data-toggle="modal" data-target="#trigger{{$investment->wholesaleInvestment->investment_investor_id}}">Investment Info</a> @else NA @endif</td>
												@endif
												<td>
													<a href="{{route('user.view.application', [base64_encode($investment->id)])}}" target="_blank">
														Application form
													</a>
													{{-- <a href="{{route('dashboard.project.application', [$investment->id])}}" target="_blank">
														View Application Form
													</a> --}}
												</td>
												<td>
													@if($investment->interested_to_buy) Yes @else No @endif
												</td>
												<td>
													@if($investment->agent_investment) <?php $agent= App\User::find($investment->user->agent_id); ?> {{ $agent->first_name }} {{ $agent->last_name }} <br> {{ $investment->user->agent_id }} @else NA @endif 
												</td>
											</tr>

											<!-- Modal for wholesale investments-->
											@if($investment->wholesaleInvestment)
											<div class="modal fade" id="trigger{{$investment->wholesaleInvestment->investment_investor_id}}" role="dialog">
												<div class="modal-dialog">
													<div class="modal-content">
														<div class="modal-header">
															<button type="button" class="close" data-dismiss="modal">&times;</button>
															<h3 class="modal-title text-center">Wholesale Investment Info</h3>
														</div>
														<div class="modal-body row" style="margin: 1px;">
															<div class="col-md-12">
																<label class="form-label"><h4>Which option closely describes you?</h4></label>
																@if($investment->wholesaleInvestment->wholesale_investing_as == 'Wholesale Investor (Net Asset $2,500,000 plus)')
																<textarea class="form-control" disabled="" style="cursor: default;">I have net assets of at least $2,500,000 or a gross income for each of the last 2 financial investors of at lease $2,50,000 a year.</textarea><br />
																<h4>Accountant's details:</h4><hr>
																<label for="asd" class="form-label"><b>Name and firm of qualified accountant</b></label>
																<input type="text" name="accountant_name_firm_txt" id="asd" class="form-control" value="@if($investment->wholesaleInvestment->accountant_name_and_firm){{$investment->wholesaleInvestment->accountant_name_and_firm}} @else No user input @endif" disabled="" style="cursor: default;"><br />
																<label for="asda" class="form-label"><b>Qualified accountant's professional body and membership designation</b></label>
																<input type="text" name="accountant_designation_txt" id="asda" class="form-control" value="@if($investment->wholesaleInvestment->accountant_professional_body_designation){{$investment->wholesaleInvestment->accountant_professional_body_designation}} @else No user input @endif" disabled="" style="cursor: default;"><br />
																<label for="asds" class="form-label"><b>Email</b></label>
																<input type="email" name="accountant_email_txt" id="asds" class="form-control" value="@if($investment->wholesaleInvestment->accountant_email){{$investment->wholesaleInvestment->accountant_email}} @else No user input @endif" disabled="" style="cursor: default;"><br />
																<label for="asdd" class="form-label"><b>Phone</b></label>
																@if($investment->wholesaleInvestment->accountant_phone)
																<input type="number" name="accountant_phone_txt" id="asdd" class="form-control" value="{{$investment->wholesaleInvestment->accountant_phone}}" disabled="" style="cursor: default;"><br />
																@else
																<input type="text" name="accountant_phone_txt" id="asdd" class="form-control" value="No user input" disabled="" style="cursor: default;"><br />
																@endif
																@elseif($investment->wholesaleInvestment->wholesale_investing_as == 'Sophisticated Investor')
																<textarea rows="3" type="text" class="form-control" disabled="" style="cursor: default;">I have experience as to: the merits of the offer; the value of the securities; the risk involved in accepting the offer; my own information needs; the adequacy of the information provided.</textarea><br />
																<h4>Experienced Investor Information:</h4><hr>
																<label for="asd" class="form-label"><b>Equity investment experience:</b></label>
																<textarea rows="4" id="asd" class="form-control" disabled="" style="cursor: default;">@if($investment->wholesaleInvestment->equity_investment_experience_text){{$investment->wholesaleInvestment->equity_investment_experience_text}} @else No user input @endif</textarea> <br />
																<label for="qwe" class="form-label"><b>How much investment experience do you have?</b></label>
																<input type="text" id="qwe" class="form-control" value="@if($investment->wholesaleInvestment->experience_period){{$investment->wholesaleInvestment->experience_period}} @else No user input @endif" disabled="" style="cursor: default;"><br />
																<label for="fgh" class="form-label"><b>What experience do you have with unlisted invesments ?</b></label>
																<textarea rows="4" id="fgh" class="form-control" disabled="" style="cursor: default;">@if($investment->wholesaleInvestment->unlisted_investment_experience_text){{$investment->wholesaleInvestment->unlisted_investment_experience_text}} @else No user input @endif</textarea> <br />
																<label for="zxc" class="form-label" style="cursor: default;"><b>Do you clearly understand the risks of investing with this offer ?</b></label>
																<textarea rows="4" id="zxc" class="form-control" disabled="" style="cursor: default;">@if($investment->wholesaleInvestment->understand_risk_text){{$investment->wholesaleInvestment->understand_risk_text}} @else No user input @endif</textarea> <br />
																@elseif($investment->wholesaleInvestment->wholesale_investing_as == 'Inexperienced Investor')
																<input type="text" class="form-control" value="I have no experience in property, securities or similar" disabled="" style="cursor: default;"><br />
																@else
																<input type="text" class="form-control" value="No user input" disabled="" style="cursor: default;"><br />
																@endif
															</div>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
														</div>
													</div>

												</div>
											</div>
											@endif

								{{-- @if($project->projectconfiguration->payment_switch)
								@else
								<div class="col-md-1" style="text-align: right;">
									<form action="{{route('dashboard.investment.confirmation', $investment->id)}}" method="POST" id="confirmationForm{{$investment->id}}">
										{{method_field('PATCH')}}
										{{csrf_field()}}
										@if($investment->investment_confirmation == 1)
										<span data-toggle="tooltip" title="Investment Confirmed"><i class="fa fa-check" aria-hidden="true" style="color: #6db980;"></i><i class="fa fa-money" aria-hidden="true" style="color: #6db980;"></i></span>
										@else
										<a id="confirmation{{$investment->id}}" data-toggle="tooltip" title="Investment Confirmation"><i class="fa fa-money" aria-hidden="true"></i></a>
										<input class="hidden" name="investment_confirmation" value="1">
										@endif
										<input type="hidden" name="investor" value="{{$investment->user->id}}">
									</form>
									<script>
										$(document).ready(function() {
											$('#confirmation{{$investment->id}}').click(function(e){
												$('#confirmationForm{{$investment->id}}').submit();
											});
										});
									</script>
								</div>
								@endif --}}
								@endif
								@endforeach
							</tbody>
						</table>
					</div>

					<div id="share_registry_tab" class="tab-pane fade" style="margin-top: 1em;overflow: auto;">
						<!-- <ul class="list-group">Hello</ul> -->
						{{-- <div>
							<div class="share-registry-actions">
								<!--<button class="btn btn-primary issue-dividend-btn" action="dividend">Issue Dividend Annualized</button>-->
								<button class="btn btn-primary issue-fixed-dividend-btn" action="fixed-dividend" style="margin: 0 1rem;">Issue Fixed Dividend</button>
								<button class="btn btn-primary repurchase-shares-btn" action="repurchase">Repurchase</button>
							</div>
							<div class="clear-both">
								<form id="declare_dividend_form" action="{{route('dashboard.investment.declareDividend', [$project->id])}}" method="POST">
									{{csrf_field()}}
									<span class="declare-statement hide"><small>Issue Dividend at <input type="number" name="dividend_percent" id="dividend_percent" step="0.01">% annual for the duration of between <input type="text" name="start_date" id="start_date" class="datepicker" placeholder="DD/MM/YYYY" readonly="readonly"> and <input type="text" name="end_date" id="end_date" class="datepicker" placeholder="DD/MM/YYYY" readonly="readonly"> : <input type="submit" class="btn btn-primary declare-dividend-btn" value="Declare"></small></span>
									<input type="hidden" class="investors-list" id="investors_list" name="investors_list">
								</form>
								<form id="declare_fixed_dividend_form" action="{{route('dashboard.investment.declareFixedDividend', [$project->id])}}" method="POST">
									{{csrf_field()}}
									<span class="declare-fixed-statement hide"><small>Issue Dividend at <input type="number" name="fixed_dividend_percent" id="fixed_dividend_percent" step="0.01"> cents per @if($project->share_vs_unit) share @else unit @endif  <input type="submit" class="btn btn-primary declare-fixed-dividend-btn" value="Declare"></small></span>
									<input type="hidden" class="investors-list" id="investors_list" name="investors_list">
								</form>
								<form id="declare_repurchase_form" action="{{route('dashboard.investment.declareRepurchase', [$project->id])}}" method="POST">
									{{csrf_field()}}
									<span class="repurchase-statement hide"><small>Repurchase @if($project->share_vs_unit) shares @else units @endif at $<input type="number" name="repurchase_rate" id="repurchase_rate" value="1" step="0.01"> per @if($project->share_vs_unit) share @else unit @endif: <input type="submit" class="btn btn-primary declare-repurchase-btn" value="Declare"></small></span>
									<input type="hidden" class="investors-list" id="investors_list" name="investors_list">
								</form>
								<form action="{{route('dashboard.investment.statement', [$project->id])}}" method="POST" class="text-center">
									{{csrf_field()}}
									<button type="submit" class="btn btn-default" id="generate_investor_statement"><b>Generate Investor Statement</b></button>
								</form>
							</div>
						</div>
						<br> --}}
						<div class="">
							<table class="table table-bordered table-striped share-registry-table" id="shareRegistryTable">
								<thead>
									<tr>
										<th>Unique ID</th>
										{{-- <th>@if($project->share_vs_unit) Share @else Unit @endif numbers</th> --}}
										{{-- <th>Project SPV Name</th> --}}
										<th>Investor Details</th>
										<th>Investment type</th>
										<th>Joint Investor <br> Name</th>
										<th>Entity details</th>
										<th>Number of @if($project->share_vs_unit) Share @else Unit @endif</th>
										<th>Share Price ($)</th>
										<th>Market Value</th>
										{{-- <th>Link to @if($project->share_vs_unit) share @else unit @endif certificate</th> --}}
										{{-- <th>TFN</th> --}}
										<th>Investment Documents</th>
										<th>Application Form</th>
										{{-- <th>Account Name</th>
										<th>BSB</th>
										<th>Account Number</th> --}}
										<th>Action</th>
										<th>Agent name</th>
									</tr>
								</thead>
								<tbody>
									@foreach($shareInvestments as $shareInvestment)
									<tr @if($shareInvestment->is_cancelled) style="color: #CCC;" @endif>
										<td data-sort="{{$shareInvestment->id}}">INV{{$shareInvestment->id}}</td>
										{{-- <td>@if($shareInvestment->share_number){{$shareInvestment->share_number}}@else{{'NA'}}@endif</td> --}}
										{{-- <td>@if($shareInvestment->project->projectspvdetail){{$shareInvestment->project->projectspvdetail->spv_name}}@endif</td> --}}
										<td><a href="{{route('dashboard.users.show', [$investment->user->id])}}" >{{$shareInvestment->user->first_name}} {{$shareInvestment->user->last_name}}</a> <br> {{$shareInvestment->user->email}} <br> {{$shareInvestment->user->phone_number}}</td>
										<td>{{$shareInvestment->investing_as}}</td>
										<td>@if($shareInvestment->investingJoint) @if($shareInvestment->investingJoint->joint_investor_first_name != '') {{$shareInvestment->investingJoint->joint_investor_first_name.' '.$shareInvestment->investingJoint->joint_investor_last_name}} @endif @else {{'NA'}} @endif</td>
										<td>@if($shareInvestment->investingJoint) @if($shareInvestment->investingJoint->investing_company) {{$shareInvestment->investingJoint->investing_company}}@endif @else{{'NA'}} @endif</td>
										{{-- <td>{{$shareInvestment->user->phone_number}}</td> --}}
										{{-- <td>{{$shareInvestment->user->email}}</td> --}}
										{{-- <td>
											@if($shareInvestment->investingJoint){{$shareInvestment->investingJoint->line_1}},@else{{$shareInvestment->user->line_1}},@endif
											@if($shareInvestment->investingJoint){{$shareInvestment->investingJoint->line_2}},@else{{$shareInvestment->user->line_2}},@endif
											@if($shareInvestment->investingJoint){{$shareInvestment->investingJoint->city}},@else{{$shareInvestment->user->city}},@endif
											@if($shareInvestment->investingJoint){{$shareInvestment->investingJoint->state}},@else{{$shareInvestment->user->state}},@endif
											@if($shareInvestment->investingJoint){{$shareInvestment->investingJoint->country}},@else{{$shareInvestment->user->country}},@endif
											@if($shareInvestment->investingJoint){{$shareInvestment->investingJoint->postal_code}}@else{{$shareInvestment->user->postal_code}}@endif

										</td> --}}
										<td>{{round($shareInvestment->amount)}}</td>
										<td>{{ number_format($shareInvestment->buy_rate, 4) }}</td>
										<td>${{ number_format($shareInvestment->amount * $shareInvestment->buy_rate,2) }}</td>
										{{-- <td>
											@if($shareInvestment->is_repurchased)
											<strong>Investment is repurchased</strong>
											@else
											@if($shareInvestment->is_cancelled)
											<strong>Investment record is cancelled</strong>
											@else
											@if($project->share_vs_unit)
											<a href="{{route('user.view.share', [base64_encode($shareInvestment->id)])}}" target="_blank">
												Share Certificate
											</a>
											@else
											<a href="{{route('user.view.unit', [base64_encode($shareInvestment->id)])}}" target="_blank">
												Unit Certificate
											</a>
											@endif
											@endif
											@endif
										</td> --}}
										{{-- <td>
											@if($shareInvestment->investingJoint){{$shareInvestment->investingJoint->tfn}} @else{{$shareInvestment->user->tfn}} @endif
										</td> --}}
										<td>
											@if($investment->userInvestmentDoc->where('type','normal_name')->last())
											<a href="/{{$investment->userInvestmentDoc->where('type','normal_name')->last()->path}}" target="_blank">{{$investment->user->first_name}} {{$investment->user->last_name}} Doc</a>
											<a href="#" class="pop">
												<img src="/{{$investment->userInvestmentDoc->where('type','normal_name')->last()->path}}" style="width: 120px; margin: auto !important;" class="img-responsive">
											</a>
											<div class="modal fade" id="imagemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
												<div class="modal-dialog">
													<div class="modal-content">
														<div class="modal-body">
															<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
															<img src="" class="imagepreview" style="width: 100%;" >
														</div>
													</div>
												</div>
											</div>
											<script>
												$(function() {
													$('.pop').on('click', function() {
														$('.imagepreview').attr('src', $(this).find('img').attr('src'));
														$('#imagemodal').modal('show');
													});
												});
											</script>
											@else
											NA
											@endif</a>
										</td>
										<td>
											<a href="{{route('user.view.application', [base64_encode($investment->id)])}}" target="_blank">
												Application form
											</a>
										</td>
										{{-- <td>@if($shareInvestment->investingJoint) {{$shareInvestment->investingJoint->account_name}} @else {{$shareInvestment->user->account_name}} @endif</td>
										<td>@if($shareInvestment->investingJoint) {{$shareInvestment->investingJoint->bsb}} @else {{$shareInvestment->user->bsb}} @endif</td>
										<td>@if($shareInvestment->investingJoint) {{$shareInvestment->investingJoint->account_number}} @else {{$shareInvestment->user->account_number}} @endif</td> --}}
										<td>
											@if($shareInvestment->is_repurchased)
											<strong>Repurchased</strong>
											@else
											@if($shareInvestment->is_cancelled)
											<strong>Cancelled</strong>
											@else
											<a href="{{route('dashboard.investment.cancel', [$shareInvestment->id])}}" class="cancel-investment">cancel</a>
											@endif
											@endif
										</td>
										<td>@if($shareInvestment->agent_investment) <?php $agent= App\User::find($shareInvestment->user->agent_id ); ?>{{ $agent->first_name}} {{ $agent->last_name}} <br> {{ $shareInvestment->user->agent_id }} @else NA @endif</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>

					</div>

					<div id="new_registry" class="tab-pane fade" style="margin-top: 1em; overflow: auto;">
						<div>
							<div class="share-registry-actions">
								<button class="btn btn-primary issue-dividend-btn" action="dividend">Issue Dividend Annualized</button>
								<button class="btn btn-primary issue-dividend-cents-per-share-btn" action="cents-per-share-dividend" style="margin: 0 1rem;">Issue Fixed Dividend</button>
								<button class="btn btn-primary issue-fixed-dividend-btn" action="fixed-dividend" style="margin: 0 1rem;">Issue Dividends cents per share</button>
								{{-- <button class="btn btn-primary repurchase-shares-btn" action="repurchase">Repurchase</button> --}}
							</div>
							<div class="clear-both">
								<form id="declare_dividend_form" action="{{route('dashboard.investment.declareDividend', [$project->id])}}" method="POST">
									{{csrf_field()}}
									<span class="declare-statement hide"><small>Issue Dividend at <input type="number" name="dividend_percent" id="dividend_percent" step="0.01">% annual for the duration of between <input type="text" name="start_date" id="start_date" class="datepicker" placeholder="DD/MM/YYYY" readonly="readonly"> and <input type="text" name="end_date" id="end_date" class="datepicker" placeholder="DD/MM/YYYY" readonly="readonly"> : <input type="submit" class="btn btn-primary declare-dividend-btn" value="Declare"></small></span>
									<input type="hidden" class="investors-list" id="investors_list" name="investors_list">
								</form>
								<form id="declare_fixed_dividend_form" action="{{route('dashboard.investment.declareFixedDividend', [$project->id])}}" method="POST">
									{{csrf_field()}}
									<span class="declare-fixed-statement hide"><small>Issue Dividend at <input type="number" name="fixed_dividend_percent" id="fixed_dividend_percent" step="0.01"> cents per @if($project->share_vs_unit) share @else unit @endif  <input type="submit" class="btn btn-primary declare-fixed-dividend-btn" value="Declare"></small></span>
									<input type="hidden" class="investors-list" id="investors_list" name="investors_list">
								</form>
								<form id="declare_cents_per_share_dividend_form" action="{{route('dashboard.investment.declareCentsPerShareDividend', [$project->id])}}" method="POST">
									{{csrf_field()}}
									<span class="declare-cents-per-share-statement hide"><small>Issue Fixed Dividend at <input type="number" name="cents_per_share_dividend" id="cents_per_share_dividend" step="0.01"> % <input type="submit" class="btn btn-primary declare-cents-per-share-dividend-btn" value="Declare"></small></span>
									<input type="hidden" class="investors-list" id="investors_list" name="investors_list">
								</form>

								<form id="declare_repurchase_form" action="{{route('dashboard.investment.declareRepurchase', [$project->id])}}" method="POST">
									{{csrf_field()}}
									<span class="repurchase-statement hide"><small>Repurchase @if($project->share_vs_unit) shares @else units @endif at $<input type="number" name="repurchase_rate" id="repurchase_rate" value="1" step="0.01"> per @if($project->share_vs_unit) share @else unit @endif: <input type="submit" class="btn btn-primary declare-repurchase-btn" value="Declare"></small></span>
									<input type="hidden" class="investors-list" id="investors_list" name="investors_list">
								</form>
								{{-- <form action="{{route('dashboard.investment.statement', [$project->id])}}" method="POST" class="pull-right">
									{{csrf_field()}}
									<button type="submit" class="btn btn-default" id="generate_investor_statement"><b>Generate Investor Statement</b></button>
								</form> --}}
							</div>
						</div>
						<br>
						<br>
						<div>
							<table class="table table-bordered table-striped new-registry-table" id="new_registry_table">
								<thead>
									<tr>
										<th class="select-check hide nosort"><input type="checkbox" class="check-all" name=""></th>
										{{-- <th>Project SPV Name</th> --}}
										<th>Investor Name</th>
										<th>Phone</th>
										<th>Email</th>
										<th>Address</th>
										<th>Number of @if($project->share_vs_unit) Shares @else Units @endif</th>
										{{-- <th>Price ($)</th> --}}
										<th>Market value ($)</th>
										<th>Link to @if($project->share_vs_unit) share @else unit @endif certificate</th>
										{{-- <th>Agent name</th> --}}
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($newRegistries as $registry)
										<tr>
											<td class="text-center select-check hide"><input type="checkbox" class="investor-check" name="" value="{{$registry->user_id}}"></td>
											{{-- <td>@if($project->projectspvdetail){{$project->projectspvdetail->spv_name}}@endif</td> --}}
											<td>{{$registry->user->first_name}} {{$registry->user->last_name}}</td>
											<td>{{$registry->user->phone_number}}</td>
											<td>{{$registry->user->email}}</td>
											<td>
												{{$registry->user->line_1}},
												{{$registry->user->line_2}},
												{{$registry->user->city}},
												{{$registry->user->state}},
												{{$registry->user->country}},
												{{$registry->user->postal_code}}
											</td>
											<td>{{ round($registry->shares) }}</td>
											{{-- <td>{{ number_format($project->share_per_unit_price, 4) }}</td> --}}
											<td>{{ number_format(($registry->shares * $project->share_per_unit_price), 2) }}</td>
											<td>
												@if($project->share_vs_unit)
												<a href="{{route('user.view.share', [base64_encode($registry->id)])}}" target="_blank">
													Share Certificate
												</a>
												@else
												<a href="{{route('user.view.unit', [base64_encode($registry->id)])}}" target="_blank">
													Unit Certificate
												</a>
												@endif
											</td>
											{{-- <td></td> --}}
											<td>
												<button type="button" class="btn btn-danger btn-sm preview-investor-statement-btn" data-investor-id="{{ $registry->user_id }}"><i class="fa fa-file-text-o" aria-hidden="true"></i> Statement</button>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
	

					{{-- <div id="transactions_tab" class="tab-pane fade" style="margin-top: 2em;overflow: auto;">
						<div>
							<table class="table table-bordered table-striped text-center" id="transactionTable">
								<thead>
									<tr>
										<th>Investor Name</th>
										<th>Project SPV Name</th>
										<th>Transaction type</th>
										<th>Date</th>
										<th>Amount</th>
										<th>Rate</th>
										<th>Number of @if($project->share_vs_unit) shares @else units @endif</th>
									</tr>
								</thead>
								<tbody>
									@foreach($transactions as $transaction)
									<tr>
										<td>{{$transaction->user->first_name}} {{$transaction->user->last_name}}</td>
										<td>@if($transaction->project->projectspvdetail){{$transaction->project->projectspvdetail->spv_name}}@endif</td>
										<td class="text-center">@if($transaction->transaction_type == "DIVIDEND") {{"ANNUALIZED DIVIDEND"}} @else {{$transaction->transaction_type}} @endif</td>
										<td>{{date('m-d-Y', strtotime($transaction->transaction_date))}}</td>
										<td>${{$transaction->amount}}</td>
										<td>{{$transaction->rate}}</td>
										<td>{{$transaction->number_of_shares}}</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<div id="positions_tab" class="tab-pane fade" style="margin-top: 2em;overflow: auto;">
						<div>
							@if(!$positions->isempty())
							<p class="text-center"><b>Effective Date:</b> {{date('m-d-Y', strtotime($positions->first()->first()->effective_date))}}</p>
							<p class="text-center"><a href="{{route('dashboard.investment.statement.send', [$project->id])}}" class="btn btn-primary" id="confirm_and_send_btn">CONFIRM AND SEND</a></p>
							@endif
							<table class="table table-bordered table-striped" id="positionTable">
								<thead>
									<tr>
										<th>Investor Name</th>
										<th>Project SPV Name</th>
										<th>Number of @if($project->share_vs_unit) shares @else units @endif</th>
										<th>Current Value</th>
									</tr>
								</thead>
								<tbody>
									@foreach($positions as $userId=>$position)
									<tr>
										<td>{{$position->first()->user->first_name}} {{$position->first()->user->last_name}}</td>
										<td>@if($position->first()->project->projectspvdetail){{$position->first()->project->projectspvdetail->spv_name}}@endif</td>
										<td>{{$position->first()->number_of_shares}}</td>
										<td>{{$position->first()->current_value}}</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div> --}}
					
					<div id="expression_of_interest_tab" class="tab-pane fade" style="margin-top: 1em;overflow: auto;">
						<div>
							<table class="table table-bordered table-striped" id="expression_of_interest_table">
								<thead>
									<tr>
										<th class="text-center">User Name</th>
										<th>Application Link</th>
										{{-- <th>Offer Document</th> --}}
										<th class="text-center">User Email</th>
										<th class="text-center">User Phone Number</th>
										<th class="text-center">Amount</th>
										<th class="text-center">Investment Expected</th>
										<th class="text-center">EOI Timestamp</th>
										{{-- <th>Interested to buy</th> --}}
									</tr>
								</thead>
								<tbody class="text-center">
									@foreach($projectsEois as $projectsEoi)
									<tr>
										<td>{{$projectsEoi->user_name}}</td>
										<td id="offer_link{{$projectsEoi->id}}">
											{{-- @if($projectsEoi->offer_doc_path) --}}
											{{-- @if($projectsEoi->is_link_sent) --}}
											@if($project->investment)
											@if($project->investment->PDS_part_1_link && $project->active && !$project->is_coming_soon && !$project->is_funding_closed && !$project->eoi_button)
											@if($projectsEoi->is_link_sent)
											<a class="send-app-form-link btn btn-primary" id="send_link{{$projectsEoi->id}}" href="javascript:void(0);" data="{{$projectsEoi->id}}"{{--  onclick="sendEOIAppFormLink()" --}}><b>Resend link</b></a>
											@else
											<a class="send-app-form-link btn btn-primary" id="send_link{{$projectsEoi->id}}" href="javascript:void(0);" data="{{$projectsEoi->id}}"{{--  onclick="sendEOIAppFormLink()" --}}><b>Accept EOI</b></a>
											@endif
											@else
											<span class="text-danger"><small><small>Offer document must be uploaded before accepting the EOI request</small></small></span>
											@endif
											@endif
										</td>
										{{-- <td>
											@if($projectsEoi->offer_doc_path)
											<a href="{{$projectsEoi->offer_doc_path}}" id="uploaded_offer_doc_link{{$projectsEoi->id}}" target="_blank" download>
												{{$projectsEoi->offer_doc_name}}
											</a>
											@endif
											<div id="new_offer_doc_link{{$projectsEoi->id}}"></div>
											<form action="{{route('dashboard.upload.offerDoc')}}" class="upload_form" id="upload_form" rel="form" method="POST" enctype="multipart/form-data">
												{!! csrf_field() !!}
												<input type="file" name="offer_doc" id="offer_doc" required="required">
												{!! $errors->first('offer_doc', '<small class="text-danger">:message</small>') !!}
												<input type="hidden" name="eoi_id" value="{{$projectsEoi->id}}">
												<input type="submit" name="upload_offer_doc" id="upload_offer_doc" value="Upload" class="btn btn-primary upload-offer-doc upload_offer_doc" data="{{$projectsEoi->id}}">
											</form>
										</td> --}}
										<td>{{$projectsEoi->user_email}}</td>
										<td>{{$projectsEoi->phone_number}}</td>
										<td>${{number_format($projectsEoi->investment_amount)}}</td>
										<td>{{$projectsEoi->invesment_period}}</td>
										<td data-sort="{{date($projectsEoi->created_at)}}">{{date('Y-m-d h:m:s', strtotime($projectsEoi->created_at))}}</td>
										{{-- <td>
											@if($projectsEoi->interested_to_buy) Yes @else No @endif
										</td> --}}
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<div id="eoi_tab" class="tab-pane fade" style="margin-top: 1em;overflow: auto;">
						<div>
							<table class="table table-bordered table-striped" id="eoiTable">
								<thead>
									<tr>
										<th>User Email</th>
										<th>User Phone Number</th>
										<th>EOI Timestamp</th>
									</tr>
								</thead>
								<tbody>
									@foreach($projectsInterests as $projectsInterest)
									<tr>
										<td>{{$projectsInterest->email}}</td>
										<td>{{$projectsInterest->phone_number}}</td>
										<td>{{date('Y-m-d h:m:s', strtotime($projectsInterest->created_at))}}</td>
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

<!--Dividend confirm Modal -->
<div id="dividend_confirm_modal" class="modal fade" role="dialog">
	<div class="modal-dialog" style="width:90%;">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">CONFIRM DIVIDEND</h4>
			</div>
			<div class="modal-body" style="padding: 15px 30px;">
				<p class="text-center">
					<i><small>** Please check and confirm the below dividend details.</small></i>
				</p><br>
				<div class="text-center">
					<h2>{{$project->title}}</h2>
					<small>{{$project->location->line_1}}, {{$project->location->line_2}}, {{$project->location->city}}, {{$project->location->postal_code}},{{$project->location->country}}</small>
				</div><br>
				<table class="table-striped dividend-confirm-table" border="0" cellpadding="10">
					<tbody>
						<tr>
							<td><b>Dividend Rate: </b></td>
							<td><small><span id="modal_dividend_rate"></span>%</small></td>
						</tr>
						<tr>
							<td><b>Start Date <small>(DD/MM/YYYY)</small>: </b></td>
							<td><small><span id="modal_dividend_start_date"></span></small></td>
						</tr>
						<tr>
							<td>End Date <small>(DD/MM/YYYY)</small>:</td>
							<td><small><span id="modal_dividend_end_date"></span></small></td>
						</tr>
					</tbody>
				</table>
				<br>
				<h2 class="text-center">Dividend calculation preview</h2><br>
				<div id="calculation_preview_table" style="width: 100%; overflow-x: auto;">
					<!-- Render through JS -->
				</div>
				<br>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="submit_dividend_confirmation">Confirm</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<!--Fixed Dividend confirm Modal -->
<div id="fixed_dividend_confirm_modal" class="modal fade" role="dialog">
	<div class="modal-dialog" style="width:90%;">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">CONFIRM DIVIDEND</h4>
			</div>
			<div class="modal-body" style="padding: 15px 30px;">
				<p class="text-center">
					<i><small>** Please check and confirm the below dividend details.</small></i>
				</p><br>
				<div class="text-center">
					<h2>{{$project->title}}</h2>
					<small>{{$project->location->line_1}}, {{$project->location->line_2}}, {{$project->location->city}}, {{$project->location->postal_code}},{{$project->location->country}}</small>
				</div><br>
				<table class="table-striped dividend-confirm-table" border="0" cellpadding="10">
					<tbody>
						<tr>
							<td><b>Dividend Rate: </b></td>
							<td><small><span id="modal_fixed_dividend_rate"></span> cents per @if($project->share_vs_unit) share @else unit @endif</small></td>
						</tr>
					</tbody>
				</table>
				<br>
				<h2 class="text-center">Dividend calculation preview</h2><br>
				<div id="fixed_dividend_calculation_preview_table" style="width: 100%; overflow-x: auto;">
					<!-- Render through JS -->
				</div>
				<br>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="submit_fixed_dividend_confirmation">Confirm</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<!--Cents Per Share Dividend confirm Modal -->
<div id="cents_per_share_dividend_confirm_modal" class="modal fade" role="dialog">
	<div class="modal-dialog" style="width:90%;">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">CONFIRM DIVIDEND</h4>
			</div>
			<div class="modal-body" style="padding: 15px 30px;">
				<p class="text-center">
					<i><small>** Please check and confirm the below dividend details.</small></i>
				</p><br>
				<div class="text-center">
					<h2>{{$project->title}}</h2>
					<small>{{$project->location->line_1}}, {{$project->location->line_2}}, {{$project->location->city}}, {{$project->location->postal_code}},{{$project->location->country}}</small>
				</div><br>
				<table class="table-striped dividend-confirm-table" border="0" cellpadding="10">
					<tbody>
						<tr>
							<td><b>Dividend Rate: </b></td>
							<td><small><span id="modal_cents_per_share_dividend_rate"></span> % fixed</small></td>
						</tr>
					</tbody>
				</table>
				<br>
				<h2 class="text-center">Dividend calculation preview</h2><br>
				<div id="cents_per_share_dividend_calculation_preview_table" style="width: 100%; overflow-x: auto;">
					<!-- Render through JS -->
				</div>
				<br>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="submit_cents_per_share_dividend_confirmation">Confirm</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>


<!--Rupurchase confirm Modal -->
<div id="repurchase_confirm_modal" class="modal fade" role="dialog">
	<div class="modal-dialog" style="width:90%;">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">CONFIRM REPURCHASE</h4>
			</div>
			<div class="modal-body" style="padding: 15px 30px;">
				<p class="text-center">
					<i><small>** Please check and confirm the below repurchase details.</small></i>
				</p><br>
				<div class="text-center">
					<h2>{{$project->title}}</h2>
					<small>{{$project->location->line_1}}, {{$project->location->line_2}}, {{$project->location->city}}, {{$project->location->postal_code}},{{$project->location->country}}</small>
				</div><br>
				<table class="table-striped repurchase-confirm-table" border="0" cellpadding="10">
					<tbody>
						<tr>
							<td><b>Repurchase Rate: </b></td>
							<td><small><span id="modal_repurchase_rate"></span>$</small></td>
						</tr>
					</tbody>
				</table>
				<br>
				<h2 class="text-center">Repurchase calculation preview</h2><br>
				<div id="repurchase_calculation_preview_table" style="width: 100%; overflow-x: auto;">
					<!-- Render through JS -->
				</div>
				<br>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="submit_repurchase_confirmation">Confirm</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<!--Investor Statement Modal -->
<div id="investor_statement_modal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">CONFIRM INVESTOR STATEMENT</h4>
			</div>
			<div class="modal-body" style="padding: 15px 30px;">
				<div class="">
					<small>** Select dates to search records.</small>
					<form name="statement_search_form" action="#">
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<label for="statement_start_date"><small>Start date:</small></label>
									<input type="date" class="form-control input-sm" name="statement_start_date" id="statement_start_date" max="{{ \Carbon\Carbon::now()->toDateString() }}" required>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group">
									<label for="statement_end_date"><small>End date:</small></label>
									<input type="date" class="form-control input-sm" name="statement_end_date" id="statement_end_date" max="{{ \Carbon\Carbon::now()->toDateString() }}" required>
								</div>
							</div>
						</div>
						<button class="btn btn-danger search-statement-by-date-btn" type="submit">Search</button>
					</form>
				</div>
				<div class="hide transactions-section">
					<hr>
					<h5 class="text-center opening-balance">
						<span class="pull-left"><label>OPENING BALANCE:</label>&nbsp;&nbsp; $ <span id="statement_opening_balance"></span></span>
						<span class=""><label>SHARE PRICE:</label>&nbsp;&nbsp; $ <span id="price"></span></span>
						<span class="pull-right"><label>NUMBER OF SHARES:</label>&nbsp;&nbsp; <span id="numbers"></span></span>
					</h5>
					<br>
					<div id="investor_statement_preview_table" style="width: 100%; overflow-x: auto;">
						<!-- Render through JS -->
					</div>
					<br>
					<h5 class="text-center closing-balance">
						<span class="pull-left"><label>CLOSING BALANCE:</label>&nbsp;&nbsp; $ <span id="statement_closing_balance"></span></span>
						<span class=""><label>SHARE PRICE:</label>&nbsp;&nbsp; $ <span id="price"></span></span>
						<span class="pull-right"><label>NUMBER OF SHARES:</label>&nbsp;&nbsp; <span id="numbers"></span></span>
					</h5>
					<br>
				</div>
			</div>
			<div class="modal-footer">
				<form name="investor_statement" action="#">
					<input hidden value="" name="start_date" required>
					<input hidden value="" name="end_date" required>
					<input hidden value="" name="investor_id" required>
					<button type="submit" class="btn btn-primary" id="submit_investor_statement">Confirm</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</form>
			</div>
		</div>
	</div>
</div>
@stop

@section('js-section')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.19/api/fnAddDataAndDisplay.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("[data-toggle=tooltip").tooltip();
		// Javascript to enable link to tab
		var hash = document.location.hash;
		var prefix = "tab_";
		if (hash) {
		    $('.nav-tabs a[href="'+hash.replace(prefix,"")+'"]').tab('show');
		    window.scrollTo(0, 0);
		} 

		// Change hash for page-reload
		$('.nav-tabs a').on('shown', function (e) {
		    window.location.hash = e.target.hash.replace("#", "#" + prefix);
		    window.scrollTo(0, 0);
		});

		$('a.edit').click(function () {
			var dad = $(this).parent();
			$(this).hide();
			dad.find('input[type="text"]').show().focus();
		});

		$('input[type=text]').focusout(function() {
			var dad = $(this).parent();
			dad.submit();
		});

		$('.issue-share-certi-btn').click(function(e){
			if (confirm('Are you sure ?')) {
				console.log('confirmed');
				$(this).closest("form").submit();
				$('.loader-overlay').show();
			} else {
				e.preventDefault();
			}
		});

		$('.money-received-btn').click(function(e){
			if (confirm('Are you sure ?')) {
				console.log('confirmed');
				$(this).closest("form").submit();
				$('.loader-overlay').show();
			} else {
				e.preventDefault();
			}
		});

		$('.send-investment-reminder').click(function(e){
			if (confirm('Are you sure ?')) {
				console.log('confirmed');
			} else {
				e.preventDefault();
			}
		});

		$('.cancel-investment').click(function(e){
			if (confirm('Are you sure ?')) {
				console.log('confirmed');
			} else {
				e.preventDefault();
			}
		});

		$('#generate_investor_statement').click(function(e){
			if (confirm('Are you sure ?')) {
				console.log('confirmed');
			} else {
				e.preventDefault();
			}
		});

		$('.preview-investor-statement-btn').click(function (e) {
			let investorId = $(this).attr('data-investor-id');
			$('form[name=statement_search_form').attr('data-investor-id', investorId);
			// previewInvestmentInvestorStatement(investorId);
			$('#investor_statement_modal').modal({
				keyboard: false,
				backdrop: 'static'
			});
		});

		$('form[name=statement_search_form]').on('submit', function (e) {
			e.preventDefault();
			let startDate = ($('#statement_start_date').val() == '') ? null : $('#statement_start_date').val();
			let endDate = ($('#statement_end_date').val() == '') ? null : $('#statement_end_date').val();
			let investorId = $(this).attr('data-investor-id');
			let form = $('form[name=investor_statement]');
			form.find('input[name=start_date]').val(startDate);
			form.find('input[name=end_date]').val(endDate);
			form.find('input[name=investor_id]').val(investorId);
			$('.transactions-section').removeClass('hide');
			previewInvestmentInvestorStatement(investorId, startDate, endDate);
		});

		$('form[name=investor_statement]').on('submit', function (e) {
			e.preventDefault();
			sendInvestorStatement($(this));
		});

		$('#confirm_and_send_btn').click(function(e){
			if (confirm('Are you sure ?')) {
				console.log('confirmed');
			} else {
				e.preventDefault();
			}
		});

		//Ajax call for sending eoi application form link to user (for both send link and resend link buttons)
		$('#expression_of_interest_tab').on('click', '.send-app-form-link', function(e){
			e.preventDefault();
			var project_id = {{$project->id}};
			var eoi_id = $(this).attr('data');
			if (confirm('Are you sure?')) {
				$('.loader-overlay').show();
				$.ajax({
					url: '/dashboard/project/interest/link',
					type: 'POST',
					dataType: 'JSON',
					data: {project_id, eoi_id},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
				}).done(function(data){
					if(data){
						$('#offer_link'+eoi_id).html('<div class="text-success"><i class="fa fa-check"></i> Sent</div>');
						$('.loader-overlay').hide();
					}
				});
			}
		});

		// $('.upload_form').submit(function(e){
		// 	e.preventDefault();
		// 	$('.loader-overlay').show();
		// 	var eoi_id, offer_doc_path, offer_doc_name;
		// 	$.ajax({
		// 		url: '/dashboard/project/upload/offerdoc',
		// 		type: 'POST',
		// 		dataType: 'JSON',
		// 		data: new FormData(this),
		// 		processData: false,
		// 		contentType: false,
		// 		headers: {
		// 			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		// 		},
		// 	}).done(function(data){
		// 		console.log(data.message);
		// 		console.log(data.status);
		// 		console.log(data.eoi_id);
		// 		if(data){
		// 			$('#offer_link'+data.eoi_id).html('<a class="send-app-form-link" id="send_link'+data.eoi_id+'" href="javascript:void(0);" data="'+data.eoi_id+'"><b>Send link</b></a>');
		// 			$('#new_offer_doc_link'+data.eoi_id).html('<a href="'+data.offer_doc_path+'" target="_blank" download> '+data.offer_doc_name+'</a><i class="fa fa-check success-icon"></i>');
		// 			$('#uploaded_offer_doc_link'+data.eoi_id).hide();
		// 			$('.loader-overlay').hide();
		// 			alert(data.message);
		//         		// sendEOIAppFormLink();
		//         	}
		//         	else
		//         	{
		//         		alert('Something went wrong! Please try again.');
		//         		$('.loader-overlay').hide();
		//         	}
		//         });
		// });

		//Hide application from admin dashboard

		$('.hide-investment').on("click", function(e){
			e.preventDefault();
			var investment_id = $(this).attr('data');
			if (confirm('Are you sure you want to delete this?')) {
				$('.loader-overlay').show();
				$.ajax({
					url: '/dashboard/projects/hideInvestment',
					type: 'PATCH',
					dataType: 'JSON',
					data: {investment_id},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
				}).done(function(data){
					if(data){
						$('.loader-overlay').hide();
						$("#investorsTable").DataTable().row( $('#application' + investment_id) ).remove().draw( false );
					}
				});
			}
		});


		var shareRegistryTable = $('#shareRegistryTable').DataTable({
			"order": [[0, 'desc']],
			"iDisplayLength": 50,
			"aoColumnDefs": [
			{
				"bSortable": false,
				'aTargets': ['nosort']
			}
			],
			"fnDrawCallback": function( oSettings ) {
				if($('.share-registry-actions').hasClass('hide')){
					$('.select-check').removeClass('hide');
				}
			}
		});
		var investorsTable = $('#investorsTable').DataTable({
			"order": [2, 'desc'],
			"iDisplayLength": 25
		});
		var transactionTable = $('#transactionTable').DataTable({
			"order": [[3, 'desc']],
			"iDisplayLength": 25
		});
		var positionTable = $('#positionTable').DataTable({
			"iDisplayLength": 25
		});
		var eoiTable = $('#eoiTable').DataTable({
			"iDisplayLength": 25
		});
		var expression_of_interest_table = $('#expression_of_interest_table').DataTable({
			"iDisplayLength": 25
		});
		let newRegistryTable = $('#new_registry_table').DataTable({
			"iDisplayLength": 25
		});

		// show select checkbox for share registry
		$('.issue-dividend-btn, .repurchase-shares-btn, .issue-fixed-dividend-btn, .issue-dividend-cents-per-share-btn').click(function(e){
			$('.select-check').removeClass('hide');
			$('.share-registry-actions').addClass('hide');
			if($(this).attr('action') == "dividend"){
				$('.declare-statement').removeClass('hide');
			}else if($(this).attr('action') == "fixed-dividend"){
				$('.declare-fixed-statement').removeClass('hide');
			}else if($(this).attr('action') == "cents-per-share-dividend"){
				$('.declare-cents-per-share-statement').removeClass('hide');
			}else {
				$('.repurchase-statement').removeClass('hide');
			}

			// Selector deselect all investors
			$('.check-all').change(function(e){
				var investors = [];
				if($(this).is(":checked")){
	                $('.investor-check').prop('checked', true);
	                $('.investor-check').each(function() {
		                investors.push($(this).val());
		            });
	            }
	            else{
	                $('.investor-check').prop('checked', false);
	                investors = [];
	            }
	            $('.investors-list').val(investors.join(','));
	        });

			// Set selected investor ids in a hidden field
			$(document).on('click', '.investor-check, .check-all', function(e) {
				let investorList = $('.investors-list').val();
				let investors = [];
				if(investorList != '') {
					investors = investorList.split(',');
				}
	            $('.investor-check').each(function() {
	            	let thisInvestorId = $(this).val();
	            	let arrayIndex = $.inArray(thisInvestorId, investors);
	                if($(this).is(":checked")){
	            		if (arrayIndex == -1) {
	                    	investors.push(thisInvestorId);
						}
	                } else {
						if (arrayIndex != -1) {
							investors.splice(arrayIndex, 1);
						}
					}
	            });
	            $('.investors-list').val(investors.join(','));
	        });

			// Declare dividend
			declareDividend();
			// Declare fixed dividend
			declareFixedDividend();
			// Declare fixed dividend
			declareCentsPerShareDividend();
			// repurchase shares
			repurchaseShares();
			//Upload offer document for eoi
			// uploadEOIOfferDoc();
		});

		// Submit dividend form
		$('#submit_dividend_confirmation').on('click', function(e) {
			$('#declare_dividend_form').submit();
			$('.loader-overlay').show();
		});

		// Submit Fixed dividend form
		$('#submit_fixed_dividend_confirmation').on('click', function(e) {
			$('#declare_fixed_dividend_form').submit();
			$('.loader-overlay').show();
		});

		// Submit Cents per share dividend form
		$('#submit_cents_per_share_dividend_confirmation').on('click', function(e) {
			$('#declare_cents_per_share_dividend_form').submit();
			$('.loader-overlay').show();
		});

		// Submit repurchase form
		$('#submit_repurchase_confirmation').on('click', function(e) {
			$('#declare_repurchase_form').submit();
			$('.loader-overlay').show();
		});

		// Apply date picker to html elements to select date
		$( ".datepicker" ).datepicker({
			'dateFormat': 'dd/mm/yy'
		});
        // sendEOIAppFormLink();

    });

	function sendInvestorStatement(form) {
		$('.loader-overlay').show();
		let investorId = form.find('input[name=investor_id]').val();
		let projectId = {{ $project->id }};
		let formData = new FormData(form[0]);
		$.ajax({
			url: '/dashboard/projects/' + projectId + '/investor/' + investorId + '/statement/send',
			type: 'POST',
			dataType: 'JSON',
			data: formData,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			contentType: false,
			processData: false
		}).done(function(data){
			if (!data.status) {
				alert (data.message);
				$('.loader-overlay').hide();
				return;
			}
			alert('Investor statement is sent to investor!');
			location.reload();
		});
	}

	function previewInvestmentInvestorStatement(investor, startDate = null, endDate = null) {
		$('.loader-overlay').show();
		let projectId = {{ $project->id }};
		
		$.ajax({
			url: '/dashboard/projects/' + projectId + '/investor/' + investor + '/statement',
			type: 'POST',
			dataType: 'JSON',
			// async: false,
			data: {
				start_date: startDate,
				end_date: endDate
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
		}).done(function(data){
			if (!data.status) {
				alert (data.message);
				$('.loader-overlay').hide();
				return;
			}
			let openingInvestment = data.data.openingBalance;
			let closingInvestment = data.data.closingBalance;
			$('#statement_opening_balance').html(new Intl.NumberFormat('en-US').format(openingInvestment ? openingInvestment.balance : 0));
			$('.opening-balance #price').html(parseFloat(openingInvestment ? openingInvestment.balance_price : {{ $project->prices->first()->price ?? $project->share_per_unit_price }}).toFixed(4));
			$('.opening-balance #numbers').html(openingInvestment ? openingInvestment.shares : 0);
			$('#statement_closing_balance').html(new Intl.NumberFormat('en-US').format(closingInvestment ? closingInvestment.balance : 0));
			$('.closing-balance #price').html(parseFloat(closingInvestment ? closingInvestment.balance_price : {{ $project->prices->first()->price ?? $project->share_per_unit_price }}).toFixed(4));
			$('.closing-balance #numbers').html(closingInvestment ? closingInvestment.shares : 0);
			$('#investor_statement_preview_table').html(data.data.transactionTable);
			$('.loader-overlay').hide();
		});
	}

	// Declare dividend
	function declareDividend(){
		$('.declare-dividend-btn').click(function(e){
			e.preventDefault();
			var dividendPercent = $('#dividend_percent').val();
			dividendPercent = dividendPercent.toString();
			var startDate = new Date($('#start_date').val());
			var endDate = new Date($('#end_date').val());
			var investorsList = $('.investors-list').val();
			var project_id = {{$project->id}};

			if(dividendPercent == '' || startDate == '' || endDate == ''){
				alert('Before declaration enter dividend percent, start date and end date input fields.');
			}
			else if(investorsList == ''){
				alert('Please select atleast one @if($project->share_vs_unit) share @else unit @endif registry record.');
			}
			else {
				$('.loader-overlay').show();
				$.ajax({
					url: '/dashboard/projects/'+project_id+'/investment/previewDividend',
					type: 'POST',
					dataType: 'JSON',
					data: {
						investors_list: investorsList,
						dividend_percent: dividendPercent,
						start_date: $('#start_date').val(),
						end_date: $('#end_date').val()
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
				}).done(function(data){
					$('.loader-overlay').hide();
					if(data.status) {
						$('#calculation_preview_table').html(data.data);

						$('#modal_dividend_rate').html(dividendPercent);
						$('#modal_dividend_start_date').html($('#start_date').val());
						$('#modal_dividend_end_date').html($('#end_date').val());

						$('#dividend_confirm_modal').modal({
							keyboard: false,
							backdrop: 'static'
						});

					} else {
						alert(data.message);
					}
				});


			}
		});
	}

	// Declare fixed dividend
	function declareFixedDividend(){
		$('.declare-fixed-dividend-btn').click(function(e){
			e.preventDefault();
			var dividendPercent = $('#fixed_dividend_percent').val();
			dividendPercent = dividendPercent.toString();
			var investorsList = $('.investors-list').val();
			var project_id = {{$project->id}};

			if(dividendPercent == ''){
				alert('Before declaration please enter dividend.');
				return;
			}
			else {
				if(investorsList == ''){
					alert('Please select atleast one @if($project->share_vs_unit) share @else unit @endif registry record.');
					return;
				}
			}

			// Show confirm box
			$('.loader-overlay').show();
			$.ajax({
				url: '/dashboard/projects/'+project_id+'/investment/previewFixedDividend',
				type: 'POST',
				dataType: 'JSON',
				data: {
					investors_list: investorsList,
					dividend_percent: dividendPercent
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
			}).done(function(data){
				$('.loader-overlay').hide();
				if(data.status) {
					$('#fixed_dividend_calculation_preview_table').html(data.data);

					$('#modal_fixed_dividend_rate').html(dividendPercent);

					$('#fixed_dividend_confirm_modal').modal({
						keyboard: false,
						backdrop: 'static'
					});

				} else {
					alert(data.message);
				}
			});
		});
	}

	// Declare cents per share dividend
	function declareCentsPerShareDividend(){
		$('.declare-cents-per-share-dividend-btn').click(function(e){
			e.preventDefault();
			var dividendPercent = $('#cents_per_share_dividend').val();
			dividendPercent = dividendPercent.toString();
			var investorsList = $('.investors-list').val();
			var project_id = {{$project->id}};

			if(dividendPercent == ''){
				alert('Before declaration please enter dividend.');
				return;
			}
			else {
				if(investorsList == ''){
					alert('Please select atleast one @if($project->share_vs_unit) share @else unit @endif registry record.');
					return;
				}
			}

			// Show confirm box
			$('.loader-overlay').show();
			$.ajax({
				url: '/dashboard/projects/'+project_id+'/investment/previewCentsPerShareDividend',
				type: 'POST',
				dataType: 'JSON',
				data: {
					investors_list: investorsList,
					dividend_percent: dividendPercent
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
			}).done(function(data){
				$('.loader-overlay').hide();
				if(data.status) {
					$('#cents_per_share_dividend_calculation_preview_table').html(data.data);

					$('#modal_cents_per_share_dividend_rate').html(dividendPercent);

					$('#cents_per_share_dividend_confirm_modal').modal({
						keyboard: false,
						backdrop: 'static'
					});

				} else {
					alert(data.message);
				}
			});
		});
	}

	// repurchase shares
	function repurchaseShares(){
		$('.declare-repurchase-btn').click(function(e){
			e.preventDefault();
			var repurchaseRate = $('#repurchase_rate').val();
			repurchaseRate = repurchaseRate.toString();
			var investorsList = $('.investors-list').val();
			var project_id = '{{$project->id}}';

			if(repurchaseRate == ''){
				alert('Before declaration please enter repurchase rate.');
				return;
			}
			else {
				if(investorsList == ''){
					alert('Please select atleast one @if($project->share_vs_unit) share @else unit @endif registry record.');
					return;
				}
			}

			//Show confirm box
			$('.loader-overlay').show();
			$.ajax({
				url: '/dashboard/projects/'+project_id+'/investment/previewrepurchase',
				type: 'POST',
				dataType: 'JSON',
				data: {
					investors_list: investorsList,
					repurchase_rate: repurchaseRate
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
			}).done(function(data){
				$('.loader-overlay').hide();
				if(data.status) {
					$('#repurchase_calculation_preview_table').html(data.data);

					$('#modal_repurchase_rate').html(repurchaseRate);

					$('#repurchase_confirm_modal').modal({
						keyboard: false,
						backdrop: 'static'
					});

				} else {
					alert(data.message);
				}
			});
		});
	}

	{{--

	function sendEOIAppFormLink(){
	$('#expression_of_interest_tab').on('click', '.send-app-form-link', function(e){
		e.preventDefault();
		var project_id = {{$project->id}};
		var eoi_id = $(this).attr('data');
		if (confirm('Are you sure you want to delete this?')) {
			$('.loader-overlay').show();
			$.ajax({
	          	url: '/dashboard/project/interest/link',
	          	type: 'POST',
	          	dataType: 'JSON',
	          	data: {project_id, eoi_id},
	          	headers: {
	            	'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	          	},
	        }).done(function(data){
	        	if(data){
	        		$('#offer_link'+eoi_id).html('<div class="text-success"><i class="fa fa-check"></i> Sent</div>');
	        		$('.loader-overlay').hide();
	        	}
	        });
	    }
	});

	--}}
	// }

</script>
@endsection
