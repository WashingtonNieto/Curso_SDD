<?php

namespace Tests\Feature\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogPostManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->user = User::factory()->create();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Cómo gestionar la ansiedad',
            'slug' => '',
            'blog_category_id' => '',
            'excerpt' => 'Claves sencillas para el día a día.',
            'content' => '<p>Contenido del artículo.</p>',
            'status' => 'borrador',
            'published_at' => '',
            'meta_description' => 'Aprende a gestionar la ansiedad.',
        ], $overrides);
    }

    public function test_pages_load(): void
    {
        $post = BlogPost::factory()->create();

        $this->actingAs($this->user)->get(route('panel.blog.posts.index'))->assertOk()->assertSee($post->title);
        $this->actingAs($this->user)->get(route('panel.blog.posts.create'))->assertOk()->assertSee('Nuevo artículo');
        $this->actingAs($this->user)->get(route('panel.blog.posts.edit', $post))->assertOk()->assertSee($post->title);
    }

    public function test_index_shows_empty_state(): void
    {
        $this->actingAs($this->user)
            ->get(route('panel.blog.posts.index'))
            ->assertOk()
            ->assertSee('Aún no has escrito ningún artículo');
    }

    public function test_creates_post_with_generated_slug_category_and_image(): void
    {
        $category = BlogCategory::factory()->create();

        $this->actingAs($this->user)
            ->post(route('panel.blog.posts.store'), $this->payload([
                'blog_category_id' => $category->id,
                'status' => 'publicado',
                'image' => UploadedFile::fake()->image('portada.jpg', 1200, 800),
            ]))
            ->assertRedirect(route('panel.blog.posts.index'))
            ->assertSessionHas('toast');

        $post = BlogPost::sole();

        $this->assertSame('como-gestionar-la-ansiedad', $post->slug);
        $this->assertSame($category->id, $post->blog_category_id);
        $this->assertNotNull($post->published_at);
        $this->assertNotNull($post->image_path);
        Storage::disk('public')->assertExists($post->image_path);
    }

    public function test_duplicated_slug_gets_a_suffix(): void
    {
        BlogPost::factory()->create(['slug' => 'como-gestionar-la-ansiedad']);

        $this->actingAs($this->user)->post(route('panel.blog.posts.store'), $this->payload());

        $this->assertTrue(BlogPost::where('slug', 'como-gestionar-la-ansiedad-2')->exists());
    }

    public function test_malicious_html_is_removed(): void
    {
        $this->actingAs($this->user)->post(route('panel.blog.posts.store'), $this->payload([
            'content' => '<p>Hola</p><script>alert(1)</script><img src="/storage/x.jpg" onerror="alert(2)"><a href="javascript:alert(3)">enlace</a>',
        ]));

        $content = BlogPost::sole()->content;

        $this->assertStringContainsString('<p>Hola</p>', $content);
        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('onerror', $content);
        $this->assertStringNotContainsString('javascript:', $content);
    }

    public function test_validation_errors(): void
    {
        $this->actingAs($this->user)
            ->post(route('panel.blog.posts.store'), $this->payload([
                'title' => '',
                'status' => 'otro',
                'meta_description' => str_repeat('a', 161),
            ]))
            ->assertSessionHasErrors(['title', 'status', 'meta_description']);

        $this->assertSame(0, BlogPost::count());
    }

    public function test_updates_post_and_replaces_image(): void
    {
        $old = UploadedFile::fake()->image('vieja.jpg')->store('uploads/blog', 'public');
        $post = BlogPost::factory()->create(['image_path' => $old, 'slug' => 'mi-articulo']);

        $this->actingAs($this->user)
            ->put(route('panel.blog.posts.update', $post), $this->payload([
                'title' => 'Título nuevo',
                'slug' => 'mi-articulo',
                'image' => UploadedFile::fake()->image('nueva.png'),
            ]))
            ->assertRedirect(route('panel.blog.posts.edit', $post));

        $post->refresh();

        $this->assertSame('Título nuevo', $post->title);
        $this->assertSame('mi-articulo', $post->slug);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($post->image_path);
    }

    public function test_removes_image_when_requested(): void
    {
        $old = UploadedFile::fake()->image('vieja.jpg')->store('uploads/blog', 'public');
        $post = BlogPost::factory()->create(['image_path' => $old]);

        $this->actingAs($this->user)->put(route('panel.blog.posts.update', $post), $this->payload(['remove_image' => '1']));

        $this->assertNull($post->refresh()->image_path);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_deletes_post_and_its_image(): void
    {
        $image = UploadedFile::fake()->image('portada.jpg')->store('uploads/blog', 'public');
        $post = BlogPost::factory()->create(['image_path' => $image]);

        $this->actingAs($this->user)
            ->delete(route('panel.blog.posts.destroy', $post))
            ->assertRedirect(route('panel.blog.posts.index'));

        $this->assertModelMissing($post);
        Storage::disk('public')->assertMissing($image);
    }

    public function test_filters_by_status_category_and_text(): void
    {
        $category = BlogCategory::factory()->create();
        BlogPost::factory()->create(['title' => 'Autoestima en la adolescencia', 'blog_category_id' => $category->id]);
        BlogPost::factory()->create(['title' => 'Borrador sobre el duelo', 'status' => 'borrador', 'published_at' => null]);

        $this->actingAs($this->user)->get(route('panel.blog.posts.index', ['estado' => 'borrador']))
            ->assertSee('Borrador sobre el duelo')
            ->assertDontSee('Autoestima en la adolescencia');

        $this->actingAs($this->user)->get(route('panel.blog.posts.index', ['categoria' => $category->id]))
            ->assertSee('Autoestima en la adolescencia')
            ->assertDontSee('Borrador sobre el duelo');

        $this->actingAs($this->user)->get(route('panel.blog.posts.index', ['categoria' => 'sin-categoria']))
            ->assertSee('Borrador sobre el duelo')
            ->assertDontSee('Autoestima en la adolescencia');

        $this->actingAs($this->user)->get(route('panel.blog.posts.index', ['q' => 'duelo']))
            ->assertSee('Borrador sobre el duelo')
            ->assertDontSee('Autoestima en la adolescencia');
    }

    public function test_guest_is_redirected(): void
    {
        $this->get(route('panel.blog.posts.index'))->assertRedirect(route('login'));
        $this->post(route('panel.blog.posts.store'), $this->payload())->assertRedirect(route('login'));
    }
}
