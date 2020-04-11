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
			@include('partials.sidebar', ['active'=>12])
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
			<h3 class="text-center">My Redemption Requests</h3>
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
                                <td>
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
@stop

@section('js-section')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		let usersTable = $('#redemptionsTable').DataTable({
			"order": [],
			"iDisplayLength": 20
		});
	});
</script>
@stop
