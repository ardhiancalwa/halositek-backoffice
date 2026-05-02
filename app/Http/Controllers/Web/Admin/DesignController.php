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
    public function index(Request $request): Factory|View
    {
        $selectedStyle = strtolower(trim((string) $request->query('style', '')));
        $selectedStyle = ProjectStyle::tryFrom($selectedStyle)?->value;

        $styleFilters = array_map(
            static fn (ProjectStyle $style): array => [
                'value' => $style->value,
                'label' => $style->name,
            ],
            ProjectStyle::cases()
        );

        $query = Project::query()
            ->with('architect')
            ->latest();

        if ($selectedStyle !== null) {
            $query->where('style', $selectedStyle);
        }

        $projects = $query
            ->paginate(12)
            ->withQueryString();

        return view('admin.pages.dashboard.design.index', [
            'projects' => $projects,
            'selectedStyle' => $selectedStyle,
            'styleFilters' => $styleFilters,
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
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['string'],
            'delete_layout_images' => ['nullable', 'array'],
            'delete_layout_images.*' => ['string'],
        ]);

        $existingImages = is_array($project->images) ? $project->images : [];
        if ($request->has('delete_images')) {
            $toDelete = $request->input('delete_images', []);
            foreach ($toDelete as $existingPath) {
                Storage::disk('public')->delete($existingPath);
                $existingImages = array_filter($existingImages, fn ($path) => $path !== $existingPath);
            }
            $data['images'] = array_values($existingImages);
        } else {
            $data['images'] = $existingImages;
        }

        if ($request->hasFile('images')) {
            $newImages = collect($request->file('images', []))
                ->map(fn ($image): string => $image->store('projects/images', 'public'))
                ->all();

            $data['images'] = array_merge($data['images'] ?? [], $newImages);
        }

        $existingLayouts = is_array($project->layout_images) ? $project->layout_images : [];
        if ($request->has('delete_layout_images')) {
            $toDelete = $request->input('delete_layout_images', []);
            foreach ($toDelete as $existingPath) {
                Storage::disk('public')->delete($existingPath);
                $existingLayouts = array_filter($existingLayouts, fn ($path) => $path !== $existingPath);
            }
            $data['layout_images'] = array_values($existingLayouts);
        } else {
            $data['layout_images'] = $existingLayouts;
        }

        if ($request->hasFile('layout_images')) {
            $newLayouts = collect($request->file('layout_images', []))
                ->map(fn ($image): string => $image->store('projects/layouts', 'public'))
                ->all();

            $data['layout_images'] = array_merge($data['layout_images'] ?? [], $newLayouts);
        }

        unset($data['delete_images'], $data['delete_layout_images']);

        $project->fill($data);
        $project->save();

        if ($request->input('source') === 'architects_modal') {
            return redirect()
                ->route('admin.dashboard.architects.index', ['type' => 'design'])
                ->with('success', 'Design status updated successfully.');
        }

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
