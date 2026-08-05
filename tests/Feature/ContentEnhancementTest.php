<?php

namespace Tests\Feature;

use App\Models\Infographic;
use App\Models\Link;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_author_without_deleting_authored_posts(): void
    {
        $admin = $this->user('admin@example.test', 'admin');
        $author = $this->user('author@example.test', 'author');
        $post = Post::query()->create([
            'author_id' => $author->id,
            'author_name' => $author->name,
            'title' => 'Artikel Guru',
            'slug' => 'artikel_guru',
            'category' => 'artikel',
            'content' => '<p>Isi artikel.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $author))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $author->id]);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'author_id' => null,
            'author_name' => $author->name,
        ]);
    }

    public function test_administrator_account_cannot_be_deleted_from_author_management(): void
    {
        $admin = $this->user('admin@example.test', 'admin');
        $otherAdmin = $this->user('other-admin@example.test', 'admin');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $otherAdmin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }

    public function test_information_post_accepts_and_displays_downloadable_attachment(): void
    {
        Storage::fake('public');
        $admin = $this->user('admin@example.test', 'admin');

        $this->actingAs($admin)->post(route('admin.posts.store'), [
            'title' => 'Informasi Formulir',
            'category' => 'informasi',
            'content' => '<p>Silakan unduh formulir.</p>',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d H:i:s'),
            'attachment' => UploadedFile::fake()->create('formulir.pdf', 120, 'application/pdf'),
        ])->assertRedirect(route('admin.posts.index'));

        $post = Post::query()->where('category', 'informasi')->sole();
        Storage::disk('public')->assertExists($post->attachment);
        $this->assertSame('formulir.pdf', $post->attachment_name);

        $this->get(route('posts.information'))->assertOk()->assertSee('Informasi Formulir');
        $bufferLevel = ob_get_level();
        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('formulir.pdf')
            ->assertSee('Unduh');
        $this->assertSame($bufferLevel, ob_get_level(), json_encode(ob_list_handlers()));
    }

    public function test_news_detail_contains_autoplay_infographic_sidebar(): void
    {
        $admin = $this->user('admin@example.test', 'admin');
        $post = Post::query()->create([
            'author_id' => $admin->id,
            'author_name' => $admin->name,
            'title' => 'Berita Madrasah',
            'slug' => 'berita_madrasah',
            'category' => 'berita',
            'content' => '<p>Isi berita.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);
        Infographic::query()->create([
            'title' => 'Infografis Siswa',
            'slug' => 'infografis_siswa',
            'image' => 'demo/infographic-students.svg',
            'published_at' => now(),
            'active' => true,
            'sort_order' => 1,
        ]);

        $bufferLevel = ob_get_level();
        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('articleInfographicCarousel')
            ->assertSee('data-bs-ride="carousel"', false)
            ->assertSee('Infografis Siswa');
        $this->assertSame($bufferLevel, ob_get_level(), json_encode(ob_list_handlers()));
    }

    public function test_active_services_are_rendered_dynamically_on_homepage(): void
    {
        Link::query()->create([
            'name' => 'SPMB',
            'description' => 'Seleksi Murid Baru',
            'url' => '/informasi',
            'icon' => 'bi-person-plus',
            'sort_order' => 1,
            'active' => true,
            'new_tab' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('dynamic-services')
            ->assertSee('Seleksi Murid Baru');
    }

    private function user(string $email, string $role): User
    {
        return User::query()->create([
            'name' => $role === 'admin' ? 'Administrator' : 'Penulis',
            'email' => $email,
            'password' => 'password-rahasia',
            'role' => $role,
            'auth_provider' => 'local',
            'active' => true,
        ]);
    }
}
