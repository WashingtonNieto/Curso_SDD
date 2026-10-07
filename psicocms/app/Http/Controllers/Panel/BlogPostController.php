<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\BlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\HtmlSanitizer;
use App\Services\ImageUploader;
use App\Support\Slug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public const NO_CATEGORY = 'sin-categoria';

    public function __construct(
        private readonly HtmlSanitizer $sanitizer,
        private readonly ImageUploader $uploader,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', Rule::in([self::NO_CATEGORY, ...BlogCategory::pluck('id')->map(fn ($id) => (string) $id)])],
            'estado' => ['nullable', Rule::in(array_keys(BlogPost::STATUSES))],
        ]);

        $posts = BlogPost::query()
            ->with('category')
            ->when($filters['q'] ?? null, function (Builder $query, string $text) {
                $text = trim($text);
                $query->where(fn (Builder $query) => $query->where('title', 'like', "%$text%")->orWhere('excerpt', 'like', "%$text%"));
            })
            ->when($filters['categoria'] ?? null, fn (Builder $query, string $category) => $category === self::NO_CATEGORY
                ? $query->whereNull('blog_category_id')
                : $query->where('blog_category_id', $category))
            ->when($filters['estado'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('panel.blog.posts.index', [
            'posts' => $posts,
            'filters' => $filters,
            'stats' => [
                'total' => BlogPost::count(),
                'published' => BlogPost::where('status', 'publicado')->count(),
                'drafts' => BlogPost::where('status', 'borrador')->count(),
            ],
            'categoryOptions' => $this->categoryOptions() + [self::NO_CATEGORY => 'Sin categoría'],
        ]);
    }

    public function create(): View
    {
        return view('panel.blog.posts.create', [
            'post' => new BlogPost(['status' => 'borrador']),
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function store(BlogPostRequest $request): RedirectResponse
    {
        $post = new BlogPost;
        $this->fill($post, $request);
        $post->save();

        return redirect()->route('panel.blog.posts.index')->with('toast', [
            'type' => 'success',
            'message' => $post->status === 'publicado'
                ? 'Artículo «'.$post->title.'» publicado correctamente.'
                : 'Artículo «'.$post->title.'» guardado como borrador.',
        ]);
    }

    public function edit(BlogPost $post): View
    {
        return view('panel.blog.posts.edit', [
            'post' => $post,
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function update(BlogPostRequest $request, BlogPost $post): RedirectResponse
    {
        $this->fill($post, $request);
        $post->save();

        return redirect()->route('panel.blog.posts.edit', $post)->with('toast', [
            'type' => 'success',
            'message' => 'Artículo actualizado correctamente.',
        ]);
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $this->uploader->delete($post->image_path);
        $post->delete();

        return redirect()->route('panel.blog.posts.index')->with('toast', [
            'type' => 'success',
            'message' => 'Artículo «'.$post->title.'» eliminado.',
        ]);
    }

    private function fill(BlogPost $post, BlogPostRequest $request): void
    {
        $data = $request->postData();
        $data['content'] = $this->sanitizer->clean($data['content']);
        $data['slug'] = Slug::unique(BlogPost::class, $request->input('slug') ?: $data['title'], $post->id, 'articulo');

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->uploader->store($request->file('image'), 'blog', $post->image_path);
        } elseif ($request->boolean('remove_image')) {
            $this->uploader->delete($post->image_path);
            $data['image_path'] = null;
        }

        $post->fill($data);
    }

    private function categoryOptions(): array
    {
        return BlogCategory::orderBy('name')->pluck('name', 'id')->all();
    }
}
