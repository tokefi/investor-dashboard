@extends('layouts.main')

@section('title-section')
Reporting | Dashboard | @parent
@stop

@section('css-section')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.9/css/jquery.dataTables.min.css">
@stop

@section('content-section')
<div class="container-fluid">
    <br>

    <div class="row">
        <div class="col-md-8 col-md-offset-2" style="">
            <ul class="nav nav-tabs" style="margin-top: 0.8em; width: 100%;">
                <li class="active" style="width: 50%;">
                    <a data-toggle="tab" href="#profile_tab" style="padding: 0em 2em"><h3 class="text-center">Transactions</h3></a>
                </li>
                <li class="" style="width: 50%;">
                    <a data-toggle="tab" href="#kyc_tab" style="padding: 0em 2em"><h3 class="text-center">Positions</h3></a>
                </li>
            </ul>
        </div>
    </div>
    {{-- <h2 class="text-center">Reporting</h2><br> --}}
    <div class="tab-content">
        <div id="profile_tab" class="tab-pane fade in active" style="overflow: auto; margin-top: 1em;">
            <form action="{{ route('dashboard.reporting') }}" method="GET">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-8 col-md-offset-2 well" style="border-top: 0px solid;">
                        <h4>FILTERS</h4><br>
                        <label>Select Transaction Types:</label>
                        <div class="row">
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" name="tx_all" > &nbsp;<small>ALL</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::BUY }}" @if(in_array(\App\Transaction::BUY, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::BUY }}</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::REPURCHASE }}" @if(in_array(\App\Transaction::REPURCHASE, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::REPURCHASE }}</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::CANCELLED }}" @if(in_array(\App\Transaction::CANCELLED, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::CANCELLED }}</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::DIVIDEND }}" @if(in_array(\App\Transaction::DIVIDEND, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::DIVIDEND }}</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::FIXED_DIVIDEND }}" @if(in_array(\App\Transaction::FIXED_DIVIDEND, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::FIXED_DIVIDEND }}</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::ANNUALIZED_DIVIDEND }}" @if(in_array(\App\Transaction::ANNUALIZED_DIVIDEND, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::ANNUALIZED_DIVIDEND }}</small></label><br>
                            </div>
                        </div>
                        <br>
                        <label>Select Project:</label>
                        <div class="row">
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" name="projects_all" > &nbsp;<small>ALL</small></label><br>
                            </div>
                            @foreach ($projects as $project)
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="project" name="project[]" value="{{ $project->id }}" @if(in_array($project->id, $projectIds)) {{ 'checked' }} @endif> &nbsp;<small>{{ $project->title }}</small></label><br>
                            </div>
                            @endforeach
                        </div><br>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="start_date"><small>Start date:</small></label>
                                    <input type="date" class="form-control input-sm" name="start_date" id="start_date" max="{{ \Carbon\Carbon::now()->toDateString() }}" placeholder="dd/mm/yyyy" value="{{ $startDate }}" required>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="end_date"><small>End date:</small></label>
                                    <input type="date" class="form-control input-sm" name="end_date" id="end_date" max="{{ \Carbon\Carbon::now()->toDateString() }}" placeholder="dd/mm/yyyy" value="{{ $endDate }}" required>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="text-center">
                            <button type="submit" class="btn btn-danger">Search <i class="fa fa-search" aria-hidden="true"></i></button>
                        </div>
                    </div>
                </div>
            </form>
            @if (empty($projectIds) && !$transactions->count())
            <div class="alert alert-danger text-center">
                No Records found for given search.
            </div>
            @endif
            @if ($transactions->count())
            <div class="pull-right">
                <h4>Summary:</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>BUY</th>
                            <th>REPURCHASE</th>
                            <th>DIVIDEND</th>
                            <th>CANCELLED</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Count: </td>
                            <td class="text-center">{{ $buy['count'] }}</td>
                            <td class="text-center">{{ $repurchase['count'] }}</td>
                            <td class="text-center">{{ $dividend['count'] }}</td>
                            <td class="text-center">{{ $cancelled['count'] }}</td>
                        </tr>
                        <tr>
                            <td>Sum ($): </td>
                            <td class="text-center">{{ number_format($buy['sum'], 2) }}</td>
                            <td class="text-center">{{ number_format($repurchase['sum'], 2) }}</td>
                            <td class="text-center">{{ number_format($dividend['sum'], 2) }}</td>
                            <td class="text-center">{{ number_format($cancelled['sum'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <br>
            @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        @if ($transactions->count())
                        <table class="table table-bordered table-striped" id="reportingTable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Transaction Type</th>
                                    <th>Number of shares</th>
                                    <th>Price</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $transaction)
                                <tr>
                                    <td><a href="{{ route('users.show', ['user' => $transaction->user_id]) }}">{{ $transaction->user->first_name }} {{ $transaction->user->last_name }}</a></td>
                                    <td>{{ $transaction->transaction_description ?? $transaction->transaction_type }}</td>
                                    <td>{{ (strpos($transaction->transaction_type, 'DIVIDEND') === false) ? $transaction->number_of_shares : '-' }}</td>
                                    <td>{{ (strpos($transaction->transaction_type, 'DIVIDEND') === false) ? '$ ' . number_format($transaction->rate, 4) : '-' }}</td>
                                    <td>
                                        @if( (strpos($transaction->transaction_type, 'DIVIDEND') === false) && ($transaction->transaction_type != \App\Transaction::REPURCHASE) && ($transaction->transaction_type != \App\Transaction::CANCELLED))
                                        $ {{ number_format($transaction->amount, 2) }}
                                        @else
                                        $ ({{ number_format($transaction->amount, 2) }})
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d-m-Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>
            </div>
            <br>
            @if ($transactions->count())
            <div class="pull-right">
                <h4>Summary:</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <th>BUY</th>
                            <th>REPURCHASE</th>
                            <th>DIVIDEND</th>
                            <th>CANCELLED</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Count: </td>
                            <td class="text-center">{{ $buy['count'] }}</td>
                            <td class="text-center">{{ $repurchase['count'] }}</td>
                            <td class="text-center">{{ $dividend['count'] }}</td>
                            <td class="text-center">{{ $cancelled['count'] }}</td>
                        </tr>
                        <tr>
                            <td>Sum ($): </td>
                            <td class="text-center">{{ number_format($buy['sum'], 2) }}</td>
                            <td class="text-center">{{ number_format($repurchase['sum'], 2) }}</td>
                            <td class="text-center">{{ number_format($dividend['sum'], 2) }}</td>
                            <td class="text-center">{{ number_format($cancelled['sum'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        <div id="kyc_tab" class="tab-pane fade " style="margin-top: 1em; overflow: auto;">
            <form action="{{ route('dashboard.registry.reporting') }}" method="GET">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-8 col-md-offset-2 well" style="border-top: 0px solid;">
                        <h4>FILTERS</h4><br>
                        {{-- <label>Select Transaction Types:</label> --}}
                        {{-- <div class="row">
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" name="tx_all" > &nbsp;<small>ALL</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::BUY }}" @if(in_array(\App\Transaction::BUY, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::BUY }}</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::REPURCHASE }}" @if(in_array(\App\Transaction::REPURCHASE, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::REPURCHASE }}</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::CANCELLED }}" @if(in_array(\App\Transaction::CANCELLED, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::CANCELLED }}</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::DIVIDEND }}" @if(in_array(\App\Transaction::DIVIDEND, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::DIVIDEND }}</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::FIXED_DIVIDEND }}" @if(in_array(\App\Transaction::FIXED_DIVIDEND, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::FIXED_DIVIDEND }}</small></label><br>
                            </div>
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="tx-type" name="tx_type[]" value="{{ \App\Transaction::ANNUALIZED_DIVIDEND }}" @if(in_array(\App\Transaction::ANNUALIZED_DIVIDEND, $txTypes)) {{ 'checked' }} @endif> &nbsp;<small>{{ \App\Transaction::ANNUALIZED_DIVIDEND }}</small></label><br>
                            </div>
                        </div> --}}
                        <label>Select Project:</label>
                        <div class="row">
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" name="projects_all" > &nbsp;<small>ALL</small></label><br>
                            </div>
                            @foreach ($projects as $project)
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="project" name="project[]" value="{{ $project->id }}" @if(in_array($project->id, $projectIds)) {{ 'checked' }} @endif> &nbsp;<small>{{ $project->title }}</small></label><br>
                            </div>
                            @endforeach
                        </div><br>
                        <br>
                        <label>Select custodian</label>
                        <div class="row">
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" name="custodians_all" > &nbsp;<small>ALL</small></label><br>
                            </div>
                            @foreach ($projects as $project)
                            @if($project->custodian)
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="custodian" name="custodian[]" value="{{ $project->custodian }}" @if(in_array($project->custodian, $projectCustodians)) {{ 'checked' }} @endif> &nbsp;<small>{{ $project->custodian }}</small></label><br>
                            </div>
                            @endif
                            @endforeach
                        </div><br>
                        <br>
                        <label>Select Responsible  Entity</label>
                        <div class="row">
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" name="responsibles_all" > &nbsp;<small>ALL</small></label><br>
                            </div>
                            @foreach ($projects as $project)
                            @if($project->responsible_entity)
                            <div class="col-xs-6 col-sm-3">
                                <label><input type="checkbox" class="responsible" name="responsible[]" value="{{ $project->responsible_entity }}" @if(in_array($project->responsible_entity, $projectResponsibles)) {{ 'checked' }} @endif> &nbsp;<small>{{ $project->responsible_entity }}</small></label><br>
                            </div>
                            @endif
                            @endforeach
                        </div><br>
                        <br>
                        <div class="text-center">
                            <button type="submit" class="btn btn-danger">Search <i class="fa fa-search" aria-hidden="true"></i></button>
                        </div>
                    </div>
                </div>
            </form>
            @if (empty($projectIds) && !$transactions->count())
            <div class="alert alert-danger text-center">
                No Records found for given search.
            </div>
            @endif
        </div>
    </div>
</div>




@endsection


@section('js-section')
<script type="text/javascript" src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
    $(document).ready(function (e) {

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

        $('#reportingTable').DataTable({
            "iDisplayLength": 100,
            // "order": [],
            "language": {
                "search": "",
                "searchPlaceholder": "Search",
            }
        });

        // Selector deselect all types
        $('input[name=tx_all]').change(function(e){
            if ($(this).is(":checked")) {
                $('.tx-type').prop('checked', true);
                return;
            }
            $('.tx-type').prop('checked', false);
        });

        // Selector deselect all projects
        $('input[name=projects_all]').change(function(e){
            if ($(this).is(":checked")) {
                $('.project').prop('checked', true);
                return;
            }
            $('.project').prop('checked', false);
        });

    });
</script>
@stop