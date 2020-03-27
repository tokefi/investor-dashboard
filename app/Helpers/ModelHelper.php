<?php

namespace App\Helpers;

use App\InvestmentInvestor;

class ModelHelper
{
    public static function getTotalInvestmentByUsersAndProject($users, $projectId)
    {
        return InvestmentInvestor::whereIn('user_id', $users)
            ->where('project_id', $projectId)
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id')
            ->get();
    }

    public static function getTotalInvestmentByUser($userId)
    {
        return InvestmentInvestor::where('user_id', $userId)
            ->where('project_site', url())
            ->where('accepted', 1)
            ->where('is_cancelled', false)
            ->select(['*', 'user_id', \DB::raw("SUM(amount) as shares")])
            ->groupBy('user_id')
            ->get();
    }
}

?>