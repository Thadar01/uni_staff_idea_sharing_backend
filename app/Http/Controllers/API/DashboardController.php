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
        $academicYear = $request->query('academicYear');

        // 1. Top Stats Cards
        $statsCards = [
            'totalIdeas' => $this->getTotalIdeas($academicYear),
            'totalContributors' => $this->getTotalContributors($academicYear),
            'anonymousIdeas' => $this->getAnonymousIdeasCount($academicYear),
            'ideasWithoutComments' => $this->getIdeasWithoutCommentsCount($academicYear),
        ];

        // 2. Ideas by Department
        $ideasByDepartment = $this->getIdeasByDepartment($academicYear);

        // 3. Ideas by Category
        $ideasByCategory = $this->getIdeasByCategory($academicYear);

        // 4. Ideas by Month (Trends)
        $ideasByMonth = $this->getIdeasByMonth($academicYear);

        // 5. Contributor Trends (Unique staff per month)
        $contributorTrends = $this->getContributorTrends($academicYear);

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

    private function getTotalIdeas($academicYear)
    {
        $query = Idea::query()->where('status', '!=', 'deleted');
        if ($academicYear) {
            $query->whereHas('closureSetting', function($q) use ($academicYear) {
                $q->where('academicYear', $academicYear);
            });
        }
        return $query->count();
    }

    private function getTotalContributors($academicYear)
    {
        $query = Idea::query()->where('status', '!=', 'deleted');
        if ($academicYear) {
            $query->whereHas('closureSetting', function($q) use ($academicYear) {
                $q->where('academicYear', $academicYear);
            });
        }
        return $query->distinct('staffID')->count('staffID');
    }

    private function getAnonymousIdeasCount($academicYear)
    {
        $query = Idea::query()->where('status', '!=', 'deleted')->where('isAnonymous', true);
        if ($academicYear) {
            $query->whereHas('closureSetting', function($q) use ($academicYear) {
                $q->where('academicYear', $academicYear);
            });
        }
        return $query->count();
    }

    private function getIdeasWithoutCommentsCount($academicYear)
    {
        $query = Idea::query()->where('status', '!=', 'deleted')->doesntHave('comments');
        if ($academicYear) {
            $query->whereHas('closureSetting', function($q) use ($academicYear) {
                $q->where('academicYear', $academicYear);
            });
        }
        return $query->count();
    }

    private function getIdeasByDepartment($academicYear)
    {
        $query = DB::table('idea')
            ->join('staffs', 'idea.staffID', '=', 'staffs.staffID')
            ->join('departments', 'staffs.departmentID', '=', 'departments.departmentID')
            ->select('departments.departmentName', DB::raw('count(*) as count'))
            ->where('idea.status', '!=', 'deleted');

        if ($academicYear) {
            $query->join('closure_setting', 'idea.settingID', '=', 'closure_setting.settingID')
                  ->where('closure_setting.academicYear', $academicYear);
        }

        return $query->groupBy('departments.departmentName')->get();
    }

    private function getIdeasByCategory($academicYear)
    {
        $query = DB::table('categories')
            ->join('idea_category', 'categories.categoryID', '=', 'idea_category.categoryID')
            ->join('idea', 'idea_category.ideaID', '=', 'idea.ideaID')
            ->select('categories.categoryname', DB::raw('count(*) as count'))
            ->where('idea.status', '!=', 'deleted');

        if ($academicYear) {
            $query->join('closure_setting', 'idea.settingID', '=', 'closure_setting.settingID')
                  ->where('closure_setting.academicYear', $academicYear);
        }

        return $query->groupBy('categories.categoryname')->get();
    }

    private function getIdeasByMonth($academicYear)
    {
        $query = Idea::query()->where('status', '!=', 'deleted');
        if ($academicYear) {
            $query->whereHas('closureSetting', function($q) use ($academicYear) {
                $q->where('academicYear', $academicYear);
            });
        }

        return $query->select(
                DB::raw('MONTHNAME(created_at) as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy(DB::raw('MIN(created_at)'))
            ->get();
    }

    private function getContributorTrends($academicYear)
    {
        $query = Idea::query()->where('status', '!=', 'deleted');
        if ($academicYear) {
            $query->whereHas('closureSetting', function($q) use ($academicYear) {
                $q->where('academicYear', $academicYear);
            });
        }

        return $query->select(
                DB::raw('MONTHNAME(created_at) as month'),
                DB::raw('count(distinct staffID) as count')
            )
            ->groupBy('month')
            ->orderBy(DB::raw('MIN(created_at)'))
            ->get();
    }
}
