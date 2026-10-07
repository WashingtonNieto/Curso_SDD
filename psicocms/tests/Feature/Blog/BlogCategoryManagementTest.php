<?php

namespace Tests\Feature\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_index_lists_categories_with_post_count(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Autoestima']);
        BlogPost::factory()->count(2)->create(['blog_category_id' => $category->id]);

        $this->actingAs($this->user)
            ->get(route('panel.blog.categories.index'))
            ->assertOk()
            ->assertSee('Autoestima')
            ->assertSee('2 artículos');
    }

    public function test_creates_category_with_unique_slug(): void
    {
        BlogCategory::factory()->create(['name' => 'Otra', 'slug' => 'salud-mental']);

        $this->actingAs($this->user)
            ->post(route('panel.blog.categories.store'), ['name' => 'Salud mental', 'description' => 'Artículos generales'])
            ->assertRedirect(route('panel.blog.categories.index'))
            ->assertSessionHas('toast');

        $this->assertTrue(BlogCategory::where('name', 'Salud mental')->where('slug', 'salud-mental-2')->exists());
    }

    public function test_creates_category_by_json_from_post_form(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('panel.blog.categories.store'), ['name' => 'Duelo'])
            ->assertCreated()
            ->assertJsonPath('category.name', 'Duelo');
    }

    public function test_rejects_duplicated_name(): void
    {
        BlogCategory::factory()->create(['name' => 'Duelo']);

        $this->actingAs($this->user)
            ->postJson(route('panel.blog.categories.store'), ['name' => 'Duelo'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_updates_category(): void
    {
        $category = BlogCategory::factory()->create(['name' => 'Pareja', 'slug' => 'pareja']);

        $this->actingAs($this->user)->get(route('panel.blog.categories.edit', $category))->assertOk();

        $this->actingAs($this->user)
            ->put(route('panel.blog.categories.update', $category), ['name' => 'Relaciones y pareja', 'slug' => 'pareja'])
            ->assertRedirect(route('panel.blog.categories.index'));

        $category->refresh();
        $this->assertSame('Relaciones y pareja', $category->name);
        $this->assertSame('pareja', $category->slug);
    }

    public function test_deleting_category_keeps_its_posts_without_category(): void
    {
        $category = BlogCategory::factory()->create();
        $post = BlogPost::factory()->create(['blog_category_id' => $category->id]);

        $this->actingAs($this->user)
            ->delete(route('panel.blog.categories.destroy', $category))
            ->assertRedirect(route('panel.blog.categories.index'));

        $this->assertModelMissing($category);
        $this->assertNull($post->refresh()->blog_category_id);
    }
}
