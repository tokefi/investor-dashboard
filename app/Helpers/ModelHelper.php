<?php

namespace App\Helpers;

use App\InvestmentInvestor;
use App\RedemptionRequest;
use App\RedemptionStatus;
use App\Price;
use Carbon\Carbon;

class ModelHelper
{
    public static function getTotalInvestmentByProject($projectId)
    {
        $investment = InvestmentInvestor::where('project_id', $projectId)
            ->whereHas('project', function ($q) {
                $q->where('project_site', url());
            })
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id', 'project_id')
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
            ->whereHas('project', function ($q) {
                $q->where('project_site', url());
            })
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id', 'project_id')
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
            ->whereHas('project', function ($q) {
                $q->where('project_site', url());
            })
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id', 'project_id')
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

    public static function getTotalInvestmentByUserAndProject($userId, $projectId, $date = null)
    {
        $investment =  InvestmentInvestor::where('user_id', $userId)
            ->where('project_id', $projectId)
            ->whereHas('project', function ($q) {
                $q->where('project_site', url());
            })
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->where('share_certificate_issued_at', '<=', $date ?? Carbon::now()->toDateString())
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id', 'project_id')
            ->first();
        
        if ($investment) {
            $redemption = RedemptionRequest::select([\DB::raw("SUM(accepted_amount) as redemptions")])
            ->where('project_id', $investment->project_id)
            ->where('user_id', $investment->user_id)
            ->whereIn('status_id', [RedemptionStatus::STATUS_PARTIAL_ACCEPTANCE, RedemptionStatus::STATUS_APPROVED])
            ->where('updated_at', '<=', $date ?? Carbon::now()->toDateString())
            ->first();

            $investment->shares = $investment->shares - ($redemption->redemptions ?? 0);
            $sharePrice = (new static)->recentSharePrice($investment->project_id, $date);
            $investment->balance_price = $sharePrice ?? $investment->project->share_per_unit_price;
            $investment->balance = $investment->shares * $investment->balance_price;
        }
        
        return $investment;
    }

    public function recentSharePrice($projectId, $date = null)
    {
        $price = Price::where('project_id', $projectId)
            ->whereRaw('DATE(effective_date) <= ?', [$date ?? Carbon::now()->toDateString()])
            ->orderBy('effective_date', 'desc')
            ->first();

        return $price ? $price->price : null;
    }
}

?>