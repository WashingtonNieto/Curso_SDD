<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\BlogCategoryRequest;
use App\Models\BlogCategory;
use App\Support\Slug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(): View
    {
        return view('panel.blog.categories.index', [
            'categories' => BlogCategory::withCount('posts')->orderBy('name')->get(),
            'category' => new BlogCategory,
        ]);
    }

    public function store(BlogCategoryRequest $request): RedirectResponse|JsonResponse
    {
        $category = new BlogCategory($request->categoryData());
        $category->slug = Slug::unique(BlogCategory::class, $request->input('slug') ?: $category->name, null, 'categoria');
        $category->save();

        $message = 'Categoría «'.$category->name.'» creada.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'category' => ['id' => $category->id, 'name' => $category->name],
            ], 201);
        }

        return redirect()->route('panel.blog.categories.index')->with('toast', ['type' => 'success', 'message' => $message]);
    }

    public function edit(BlogCategory $category): View
    {
        return view('panel.blog.categories.edit', [
            'category' => $category->loadCount('posts'),
        ]);
    }

    public function update(BlogCategoryRequest $request, BlogCategory $category): RedirectResponse
    {
        $category->fill($request->categoryData());
        $category->slug = Slug::unique(BlogCategory::class, $request->input('slug') ?: $category->name, $category->id, 'categoria');
        $category->save();

        return redirect()->route('panel.blog.categories.index')->with('toast', [
            'type' => 'success',
            'message' => 'Categoría «'.$category->name.'» actualizada.',
        ]);
    }

    public function destroy(BlogCategory $category): RedirectResponse
    {
        DB::transaction(function () use ($category) {
            $category->posts()->update(['blog_category_id' => null]);
            $category->delete();
        });

        return redirect()->route('panel.blog.categories.index')->with('toast', [
            'type' => 'success',
            'message' => 'Categoría «'.$category->name.'» eliminada.',
        ]);
    }
}
