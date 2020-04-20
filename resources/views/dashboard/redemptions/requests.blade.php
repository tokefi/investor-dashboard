@extends('layouts.main')

@section('title-section')
Projects | Dashboard | @parent
@stop

@section('css-section')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css">
@stop

@section('content-section')
<div class="container-fluid">
	<br>
	<div class="row">
        <div class="col-md-12">
            <h2 class="text-center">Redemption Requests</h2><br>
			<div class="table-responsive">
                <table class="table table-bordered table-striped" id="requestsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Project</th>
                            <th>Requested Shares</th>
                            <th>Price ($)</th>
                            <th>Amount</th>
                            <th>Requested On</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Comments</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($redemptionRequests as $redemption)
                            <tr style="@if($redemption->status_id != \App\RedemptionStatus::STATUS_PENDING) color: #ccc;  @endif">
                                <td>{{ sprintf('%05d', $redemption->id) }}</td>
                                <td>
                                    <a href="/users/{{ $redemption->user_id }}">{{ $redemption->user->first_name }} {{ $redemption->user->last_name }}</a>
                                    <br>{{$redemption->user->email}}<br>{{$redemption->user->phone_number}}
                                </td>
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
                                    @if($redemption->status_id == \App\RedemptionStatus::STATUS_REJECTED)
                                    ${{ number_format(round($redemption->request_amount * $redemption->price, 2)) }}
                                    @else
                                    ${{ number_format(round($redemption->request_amount * $redemption->project->share_per_unit_price, 2)) }}
                                    @endif
                                </td>
                                <td>{{ $redemption->created_at->toFormattedDateString() }}</td>
                                <td>
                                    {{ $redemption->type }}
                                    @if ($redemption->type == 'ROLLOVER')
                                        @if ($redemption->rollover_project_id)
                                            to
                                            <a href="{{ route('projects.show', $redemption->rollover_project_id) }}">{{ $redemption->rolloverProject->title }}</a>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    {{ $redemption->status->name }}<br>
                                    @if($redemption->status_id == \App\RedemptionStatus::STATUS_PARTIAL_ACCEPTANCE)
                                    <span class="badge"><strong>Accepted:</strong> {{ $redemption->accepted_amount }}/{{ $redemption->request_amount }}</span>
                                    @endif
                                </td>
                                <td>{{ $redemption->comments }}</td>
                                <td>
                                    @if($redemption->status_id == \App\RedemptionStatus::STATUS_PENDING)
                                         <button class="btn btn-success btn-block accept-redemption-btn" data-action="accept" data-project-id="{{ $redemption->project_id }}" data-shares="{{ $redemption->request_amount }}" data-redemption-id="{{ $redemption->id }}">Accept</button>
                                        <button class="btn btn-danger btn-block reject-redemption-btn" data-action="reject" data-project-id="{{ $redemption->project_id }}" data-redemption-id="{{ $redemption->id }}">Reject</button>
                                    @else
                                        @if (($redemption->status_id == \App\RedemptionStatus::STATUS_PARTIAL_ACCEPTANCE) ||
                                            ($redemption->status_id == \App\RedemptionStatus::STATUS_APPROVED))
                                            @if($redemption->is_money_sent)
                                                <strong class="text-success"><i class="fa fa-check"> Money Sent</i></strong>
                                            @else
                                                <button class="btn btn-primary btn-block money_sent" data-redemption-id="{{ $redemption->id }}">Money Sent</button>
                                            @endif
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Redemption accept Modal -->
<div id="redmeption_accept_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Accept Redemption</h4>
            </div>
            <form action="#" id="redemption_accept_form">
                <div class="modal-body" style="padding: 30px;">
                    <div class="form-group">
                        <label for="num_shares">Number of shares: </label>
                        <input type="number" name="num_shares" id="num_shares" min="1" max="" step="1" class="form-control" placeholder="No. of Shares" required>
                    </div>
                    <div class="form-group">
                        <label for="comments">Comments: </label>
                        <textarea rows="4" class="form-control" name="comments" id="comments"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Confirm Accept</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>    
        </div>
    </div>
</div>

<!-- Redemption reject Modal -->
<div id="redmeption_reject_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Reject Redemption</h4>
            </div>
            <form action="#" id="redemption_reject_form">
                <div class="modal-body" style="padding: 30px;">
                    <div class="form-group">
                        <label for="comments">Reason to Reject: </label>
                        <textarea rows="4" class="form-control" name="comments" id="comments" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" >Confirm Reject</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection


@section('js-section')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$('#requestsTable').DataTable({
            "iDisplayLength": 10,
            "order": []
        });

        $('.accept-redemption-btn, .reject-redemption-btn').on('click', function () {
            let action = $(this).attr('data-action');
            let shares = $(this).attr('data-shares');
            let redemptionId = $(this).attr('data-redemption-id');

            $('input[name=num_shares]').val(shares);
            $('input[name=num_shares]').attr('max', shares);
            
            if (action == 'accept') {
                let uri = "/dashboard/redemption/" + redemptionId + "/accept";
                $('#redemption_accept_form').attr('action', uri);
                $('#redmeption_accept_modal').modal({
                    keyboard: false,
                    backdrop: 'static'
                });
            } 

            if (action == 'reject') {
                let uri = "/dashboard/redemption/" + redemptionId + "/reject";
                $('#redemption_reject_form').attr('action', uri);
                $('#redmeption_reject_modal').modal({
                    keyboard: false,
                    backdrop: 'static'
                });
            }
        });

        $('#redemption_accept_form').on('submit', function (e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to accept redemption?')) {
				return;
            }
            $('.loader-overlay').show();
			let uri = $(this).attr('action');
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
				alert('Redemption Request successfully approved for ' + data.data.shares + ' shares.');
				location.reload();
			});
        });

        $('#redemption_reject_form').on('submit', function (e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to reject redemption?')) {
				return;
            }
            $('.loader-overlay').show();
			let uri = $(this).attr('action');
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
				alert('Redemption Request has been rejected!');
				location.reload();
			});
        });

        $('.money_sent').on('click', function (e) {
            if (!confirm('Are you sure you want to confirm money sent?')) {
				return;
            }
            let redemptionId = $(this).attr('data-redemption-id');

            $('.loader-overlay').show();
			let uri = '/dashboard/redemption/' + redemptionId + '/money-sent';
            let method = 'POST';
			$.ajax({
                url: uri,
                type: 'POST',
                dataType: 'JSON',
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
				alert('Money sent confirmation updated successfully!');
				location.reload();
			});
        });
	});
</script>
@stop