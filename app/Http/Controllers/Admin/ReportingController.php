<?php

namespace App\Http\Controllers\Admin;

use App\Color;
use App\Project;
use App\Transaction;
use App\Http\Requests;
use View;
use App\SiteConfiguration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportingController extends Controller
{
    protected $siteConfiguration;
    protected $color;

    /**
     * constructor for ReportingController
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
        $txTypes = $request->tx_type ?? [];
        $projectIds = $request->project ?? [];
        $buy = $repurchase = $dividend = $cancelled = [
            'count' => 0,
            'sum' => 0
        ];
        
        $transactions = Transaction::whereHas('project', function ($q) {
            $q->where('project_site', url());
        })
        ->whereIn('transaction_type', $txTypes)
        ->whereIn('project_id', $projectIds)
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

        return view('dashboard.reporting.index', [
            'color' => $this->color,
            'transactions' => $transactions,
            'projects' => $projects,
            'txTypes' => $txTypes,
            'projectIds' => $projectIds,
            'buy' => $buy,
            'repurchase' => $repurchase,
            'dividend' => $dividend,
            'cancelled' => $cancelled,
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
