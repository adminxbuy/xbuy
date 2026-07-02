<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_published_articles_and_view_by_slug()
    {
        $author = User::factory()->create(['role' => 'admin', 'phone' => '9999999999']);

        // Create a draft article and a published article
        $draft = Article::create([
            'title' => 'Draft Article',
            'slug' => 'draft-article',
            'content' => 'This is draft content',
            'author_id' => $author->id,
            'status' => 'draft',
            'is_published' => false,
            'published_at' => null,
        ]);

        $published = Article::create([
            'title' => 'Published Article',
            'slug' => 'published-article',
            'content' => 'This is published content',
            'author_id' => $author->id,
            'status' => 'published',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        // 1. Check index returns only published article
        $response = $this->getJson('/api/v1/articles');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals('published-article', $response->json('data.data.0.slug'));

        // 2. Check view details of published article works
        $showResponse = $this->getJson('/api/v1/articles/published-article');
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('success', true);
        $showResponse->assertJsonPath('data.title', 'Published Article');

        // 3. Check view details of draft article fails (since it is a draft)
        $draftResponse = $this->getJson('/api/v1/articles/draft-article');
        $draftResponse->assertStatus(404);
    }

    public function test_admin_can_perform_crud_operations_on_articles()
    {
        $admin = User::factory()->create(['role' => 'admin', 'phone' => '8888888888']);
        $nonAdmin = User::factory()->create(['role' => 'buyer', 'phone' => '7777777777']);

        // 1. Non-admin cannot create
        $response = $this->actingAs($nonAdmin, 'sanctum')->postJson('/api/admin/articles', [
            'title' => 'New Article',
            'content' => 'My content',
        ]);
        $response->assertStatus(403);

        // 2. Admin can create
        $createResponse = $this->actingAs($admin, 'sanctum')->postJson('/api/admin/articles', [
            'title' => 'New Article',
            'slug' => 'new-article-slug',
            'content' => 'My content',
            'status' => 'draft',
            'tags' => ['tech', 'laravel']
        ]);
        $createResponse->assertStatus(201);
        $this->assertDatabaseHas('articles', [
            'title' => 'New Article',
            'slug' => 'new-article-slug',
            'status' => 'draft'
        ]);
        $articleId = $createResponse->json('data.id');

        // 3. Admin can list all articles (including draft)
        $listResponse = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/articles');
        $listResponse->assertStatus(200);
        $this->assertCount(1, $listResponse->json('data.data'));

        // 4. Admin can update
        $updateResponse = $this->actingAs($admin, 'sanctum')->putJson("/api/admin/articles/{$articleId}", [
            'title' => 'Updated Article Title',
            'slug' => 'new-article-slug',
            'content' => 'Updated content',
            'status' => 'published',
            'tags' => ['tech', 'laravel', 'php']
        ]);
        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('articles', [
            'id' => $articleId,
            'title' => 'Updated Article Title',
            'status' => 'published'
        ]);

        // 5. Admin can delete
        $deleteResponse = $this->actingAs($admin, 'sanctum')->deleteJson("/api/admin/articles/{$articleId}");
        $deleteResponse->assertStatus(200);
        $this->assertSoftDeleted('articles', [
            'id' => $articleId
        ]);
    }
}
