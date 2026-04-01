<?php

namespace App\Exports;

use App\Models\Idea;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IdeaExport implements FromCollection, WithHeadings, WithMapping
{
    protected $settingID;

    public function __construct($settingID)
    {
        $this->settingID = $settingID;
    }

    public function collection()
    {
        return Idea::with(['staff', 'categories'])
            ->withCount([
                'comments',
                'reports',
                'votes as like_count' => function ($query) {
                    $query->where('voteType', 'Like');
                },
                'votes as unlike_count' => function ($query) {
                    $query->where('voteType', 'Unlike');
                }
            ])
            ->where('settingID', $this->settingID)
            ->where('status', 'approved')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Description',
            'Author',
            'Categories',
            'View Count',
            'Likes',
            'Unlikes',
            'Comments',
            'Reports',
            'Submitted At'
        ];
    }

    public function map($idea): array
    {
        $author = $idea->isAnonymous ? 'Anonymous' : ($idea->staff ? $idea->staff->staffName : 'Unknown');
        $categories = $idea->categories->pluck('categoryname')->join(', ');

        return [
            $idea->ideaID,
            $idea->title,
            // Strip HTML tags if the description is rich text, otherwise just return it
            strip_tags($idea->description),
            $author,
            $categories,
            $idea->viewCount,
            $idea->like_count ?? 0,
            $idea->unlike_count ?? 0,
            $idea->comments_count ?? 0,
            $idea->reports_count ?? 0,
            $idea->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
