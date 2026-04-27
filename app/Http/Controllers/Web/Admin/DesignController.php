<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\ProjectStyle;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;
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

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:64'],
            'style' => ['required', new Enum(ProjectStyle::class)],
            'description' => ['nullable', 'string', 'max:255'],
            'estimated_cost' => ['required', 'string', 'max:255'],
            'highlight_features' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:100'],
            'images' => ['nullable', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'layout_images' => ['nullable', 'array', 'min:1', 'max:10'],
            'layout_images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('images')) {
            foreach ((array) $project->images as $existingPath) {
                Storage::disk('public')->delete($existingPath);
            }

            $data['images'] = collect($request->file('images', []))
                ->map(fn ($image): string => $image->store('projects/images', 'public'))
                ->all();
        }

        if ($request->hasFile('layout_images')) {
            foreach ((array) $project->layout_images as $existingPath) {
                Storage::disk('public')->delete($existingPath);
            }

            $data['layout_images'] = collect($request->file('layout_images', []))
                ->map(fn ($image): string => $image->store('projects/layouts', 'public'))
                ->all();
        }

        $project->fill($data);
        $project->save();

        return redirect()
            ->route('admin.dashboard.designs.show', $project)
            ->with('success', 'Design updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        foreach ((array) $project->images as $existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        foreach ((array) $project->layout_images as $existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        $project->delete();

        return redirect()
            ->route('admin.dashboard.designs.index')
            ->with('success', 'Design deleted successfully.');
    }
}
