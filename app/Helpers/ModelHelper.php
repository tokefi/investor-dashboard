<?php

namespace App\Helpers;

use App\InvestmentInvestor;
use App\RedemptionRequest;
use App\RedemptionStatus;

class ModelHelper
{
    public static function getTotalInvestmentByProject($projectId)
    {
        $investment = InvestmentInvestor::where('project_id', $projectId)
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id')
            ->get();
        
        return $investment->map(function ($item, $key) {
            $redemption = RedemptionRequest::select([\DB::raw("SUM(accepted_amount) as redemptions")])
                ->where('project_id', $item->project_id)
                ->where('user_id', $item->user_id)
                ->first();
            $item->shares = $item->shares - $redemption->redemptions;
            return $item;
        });
    }

    public static function getTotalInvestmentByUsersAndProject($users, $projectId)
    {
        $investment = InvestmentInvestor::whereIn('user_id', $users)
            ->where('project_id', $projectId)
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id')
            ->get();
        
        return $investment->map(function ($item, $key) {
            $redemption = RedemptionRequest::select([\DB::raw("SUM(accepted_amount) as redemptions")])
                ->where('project_id', $item->project_id)
                ->where('user_id', $item->user_id)
                ->first();
            $item->shares = $item->shares - $redemption->redemptions;
            return $item;
        });
    }

    public static function getTotalInvestmentByUser($userId)
    {
        $investment =  InvestmentInvestor::where('user_id', $userId)
            ->where('project_site', url())
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id')
            ->get();

        return $investment->map(function ($item, $key) {
            $redemption = RedemptionRequest::select([\DB::raw("SUM(accepted_amount) as redemptions")])
                ->where('project_id', $item->project_id)
                ->where('user_id', $item->user_id)
                ->first();
            $item->shares = $item->shares - $redemption->redemptions;
            return $item;
        });
    }

    public static function getTotalInvestmentByUserAndProject($userId, $projectId)
    {
        $investment =  InvestmentInvestor::where('user_id', $userId)
            ->where('project_id', $projectId)
            ->where('project_site', url())
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id')
            ->first();

        $redemption = RedemptionRequest::select([\DB::raw("SUM(accepted_amount) as redemptions")])
            ->where('project_id', $investment->project_id)
            ->where('user_id', $investment->user_id)
            ->first();
        
        $investment->shares = $investment->shares - $redemption->redemptions;

        return $investment;
    }
}

?>