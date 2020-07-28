<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use View;
use App\Color;
use App\Project;
use Carbon\Carbon;
use App\Transaction;
use App\Http\Requests;
use App\SiteConfiguration;
use App\InvestmentInvestor;
use App\Http\Controllers\Controller;


class RegistryReportingController extends Controller
{

    protected $siteConfiguration;
    protected $color;

    /**
     * constructor for RegistryReportingController
     */
    public function __construct()
    {
        $this->siteConfiguration = SiteConfiguration::where('project_site', url())->first();
        $this->color = Color::where('project_site', url())->first();

        $this->allProjects = Project::where('project_site', url())->get();
        View::share('allProjects', $this->allProjects);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // dd($request->all());

        $startDate = Carbon::parse($request->start_date ?? '2019-12-01')->toDateString();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->toDateString() : Carbon::now()->toDateString();
        $txTypes = $request->tx_type ?? [];
        $projectIds = $request->project ?? [];
        $projectCustodians = $request->custodian ?? [];
        $projectResponsibles = $request->responsible ?? [];
        $buy = $repurchase = $dividend = $cancelled = [
            'count' => 0,
            'sum' => 0
        ];


        $projectIds = array_unique(array_merge($projectIds,$projectCustodians));
        $projectIds = array_unique(array_merge($projectIds,$projectResponsibles));
        // dd($projectIds);
        $transactions = Transaction::whereHas('project', function ($q) {
            $q->where('project_site', url());
        })
        ->whereIn('transaction_type', $txTypes)
        ->whereIn('project_id', $projectIds)
        ->whereRaw('DATE(created_at) BETWEEN ? AND ?', [$startDate, $endDate])
        ->get();

        foreach ($transactions as $key => $transaction) {
            switch ($transaction->transaction_type) {
                case Transaction::BUY:
                    $buy['count']++;
                    $buy['sum'] += $transaction->amount;
                    break;
                
                case Transaction::REPURCHASE:
                    $repurchase['count']++;
                    $repurchase['sum'] += $transaction->amount;
                    break;
                
                case Transaction::DIVIDEND:
                case Transaction::ANNUALIZED_DIVIDEND:
                case Transaction::FIXED_DIVIDEND:
                    $dividend['count']++;
                    $dividend['sum'] += $transaction->amount;
                    break;
                
                case Transaction::CANCELLED:
                    $cancelled['count']++;
                    $cancelled['sum'] += $transaction->amount;
                    break;

                default:
                    # code...
                    break;
            }
        }

        $projects = Project::where('project_site', url())->get();

        $registries = InvestmentInvestor::whereIn('project_id', $projectIds)
            ->whereHas('project', function ($q) {
                $q->where('project_site', url());
            })
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id', 'project_id')
            ->get();
            $projectIds = $request->project ?? [];
        return view('dashboard.registry.index', [
            'color' => $this->color,
            'transactions' => $transactions,
            'projects' => $projects,
            'txTypes' => $txTypes,
            'projectIds' => $projectIds,
            'buy' => $buy,
            'repurchase' => $repurchase,
            'dividend' => $dividend,
            'cancelled' => $cancelled,
            'startDate' => $request->start_date ?? null,
            'endDate' => $request->end_date ?? null,
            'projectCustodians'=>$projectCustodians,
            'projectResponsibles'=>$projectResponsibles,
            'registries' => $registries
        ]);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
