<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Idea;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        $settingID = $request->query('settingID');

        // 1. Top Stats Cards
        $statsCards = [
            'totalIdeas' => $this->getTotalIdeas($settingID),
            'totalContributors' => $this->getTotalContributors($settingID),
            'anonymousIdeas' => $this->getAnonymousIdeasCount($settingID),
            'ideasWithoutComments' => $this->getIdeasWithoutCommentsCount($settingID),
        ];

        // 2. Ideas by Department
        $ideasByDepartment = $this->getIdeasByDepartment($settingID);

        // 3. Ideas by Category
        $ideasByCategory = $this->getIdeasByCategory($settingID);

        // 4. Ideas by Month (Trends)
        $ideasByMonth = $this->getIdeasByMonth($settingID);

        // 5. Contributor Trends (Unique staff per month)
        $contributorTrends = $this->getContributorTrends($settingID);

        return response()->json([
            'success' => true,
            'message' => 'Dashboard statistics retrieved successfully.',
            'data' => [
                'topStats' => $statsCards,
                'ideasByDepartment' => $ideasByDepartment,
                'ideasByCategory' => $ideasByCategory,
                'ideasByMonth' => $ideasByMonth,
                'contributorTrends' => $contributorTrends
            ]
        ], 200);
    }

    private function getTotalIdeas($settingID)
    {
        $query = Idea::query()->where('status', '!=', 'deleted');
        if ($settingID) $query->where('settingID', $settingID);
        return $query->count();
    }

    private function getTotalContributors($settingID)
    {
        $query = Idea::query()->where('status', '!=', 'deleted');
        if ($settingID) $query->where('settingID', $settingID);
        return $query->distinct('staffID')->count('staffID');
    }

    private function getAnonymousIdeasCount($settingID)
    {
        $query = Idea::query()->where('status', '!=', 'deleted')->where('isAnonymous', true);
        if ($settingID) $query->where('settingID', $settingID);
        return $query->count();
    }

    private function getIdeasWithoutCommentsCount($settingID)
    {
        $query = Idea::query()->where('status', '!=', 'deleted')->doesntHave('comments');
        if ($settingID) $query->where('settingID', $settingID);
        return $query->count();
    }

    private function getIdeasByDepartment($settingID)
    {
        $query = DB::table('idea')
            ->join('staffs', 'idea.staffID', '=', 'staffs.staffID')
            ->join('departments', 'staffs.departmentID', '=', 'departments.departmentID')
            ->select('departments.departmentName', DB::raw('count(*) as count'))
            ->where('idea.status', '!=', 'deleted');

        if ($settingID) $query->where('idea.settingID', $settingID);

        return $query->groupBy('departments.departmentName')->get();
    }

    private function getIdeasByCategory($settingID)
    {
        $query = DB::table('categories')
            ->join('idea_category', 'categories.categoryID', '=', 'idea_category.categoryID')
            ->join('idea', 'idea_category.ideaID', '=', 'idea.ideaID')
            ->select('categories.categoryname', DB::raw('count(*) as count'))
            ->where('idea.status', '!=', 'deleted');

        if ($settingID) $query->where('idea.settingID', $settingID);

        return $query->groupBy('categories.categoryname')->get();
    }

    private function getIdeasByMonth($settingID)
    {
        $query = Idea::query()->where('status', '!=', 'deleted');
        if ($settingID) $query->where('settingID', $settingID);

        return $query->select(
                DB::raw('MONTHNAME(created_at) as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy(DB::raw('MIN(created_at)'))
            ->get();
    }

    private function getContributorTrends($settingID)
    {
        $query = Idea::query()->where('status', '!=', 'deleted');
        if ($settingID) $query->where('settingID', $settingID);

        return $query->select(
                DB::raw('MONTHNAME(created_at) as month'),
                DB::raw('count(distinct staffID) as count')
            )
            ->groupBy('month')
            ->orderBy(DB::raw('MIN(created_at)'))
            ->get();
    }
}
