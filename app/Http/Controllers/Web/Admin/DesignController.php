<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class DesignController extends Controller
{
    public function index(): Factory|View
    {
        $projects = Project::query()
            ->with('architect')
            ->latest()
            ->paginate(12);

        return view('admin.pages.dashboard.design.index', [
            'projects' => $projects,
        ]);
    }

    public function show(Project $project): Factory|View
    {
        $project->load('architect');

        return view('admin.pages.dashboard.design.show', [
            'project' => $project,
        ]);
    }
}
