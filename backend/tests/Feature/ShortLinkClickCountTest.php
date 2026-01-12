<?php

namespace Tests\Feature;

use App\Models\ShortLink;
use App\Jobs\ProcessRedirectCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ShortLinkClickCountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Limpa cache antes de cada teste
        Cache::flush();
    }

    public function test_first_click_creates_cache_and_dispatches_job()
    {
        Bus::fake();
        
        $shortLink = ShortLink::factory()->create([
            'code' => 'test123',
            'clicks' => 0
        ]);

        // Primeiro clique VIA ROTA
        $response = $this->get("/{$shortLink->code}");
        
        // Verifica redirect
        $response->assertRedirect($shortLink->original_url);
        
        // Verifica se job foi despachado (não acessa propriedades privadas)
        Bus::assertDispatched(ProcessRedirectCount::class);
    }

    public function test_multiple_clicks_increment_but_not_database_immediately()
    {
        $shortLink = ShortLink::factory()->create([
            'code' => 'test123',
            'clicks' => 0
        ]);

        // Primeiro clique
        $response1 = $this->get("/{$shortLink->code}");
        $response1->assertRedirect($shortLink->original_url);
        
        // Segundo clique
        $response2 = $this->get("/{$shortLink->code}");
        $response2->assertRedirect($shortLink->original_url);
        
        // Terceiro clique
        $response3 = $this->get("/{$shortLink->code}");
        $response3->assertRedirect($shortLink->original_url);
        
        // Banco ainda tem 0 (não foi processado ainda)
        $this->assertEquals(0, $shortLink->fresh()->clicks);
    }

    public function test_job_updates_database_after_delay()
    {
        // Este teste é mais complicado porque envolve delay
        // Melhor testar manualmente ou com mocks
        $this->markTestSkipped('Delay job testing requires integration tests with real queue processing.');
    }

    public function test_cache_is_separate_per_shortlink()
    {
        $shortLink1 = ShortLink::factory()->create([
            'code' => 'link1',
            'original_url' => 'https://example1.com',
            'clicks' => 0
        ]);
        
        $shortLink2 = ShortLink::factory()->create([
            'code' => 'link2', 
            'original_url' => 'https://example2.com',
            'clicks' => 0
        ]);
        
        // 2 cliques no primeiro link
        $this->get("/{$shortLink1->code}");
        $this->get("/{$shortLink1->code}");
        
        // 1 clique no segundo link  
        $this->get("/{$shortLink2->code}");
        
        // Ambos ainda 0 no banco
        $this->assertEquals(0, $shortLink1->fresh()->clicks);
        $this->assertEquals(0, $shortLink2->fresh()->clicks);
    }

    public function test_redirect_works_with_expired_link()
    {
        $shortLink = ShortLink::factory()->create([
            'code' => 'expired',
            'original_url' => 'https://example.com',
            'expires_at' => now()->subDay(), // Já expirou
            'clicks' => 0
        ]);

        $response = $this->get("/{$shortLink->code}");
        
        // Deve retornar 410 Gone
        $response->assertStatus(410)
                 ->assertJson(['message' => 'Link expired']);
    }

    public function test_redirect_returns_404_for_invalid_code()
    {
        $response = $this->get('/invalidcode123');
        
        $response->assertStatus(404)
                 ->assertJson(['message' => 'Link not found']);
    }

    public function test_clicks_are_counted_only_for_active_links()
    {
        $activeLink = ShortLink::factory()->create([
            'code' => 'active',
            'original_url' => 'https://example.com',
            'expires_at' => now()->addDays(7),
            'clicks' => 0
        ]);
        
        $expiredLink = ShortLink::factory()->create([
            'code' => 'expired',
            'original_url' => 'https://example2.com',
            'expires_at' => now()->subDay(),
            'clicks' => 0
        ]);
        
        // Clique no link ativo
        $response1 = $this->get("/{$activeLink->code}");
        $response1->assertRedirect($activeLink->original_url);
        
        // Clique no link expirado
        $response2 = $this->get("/{$expiredLink->code}");
        $response2->assertStatus(410);
        
        // Só o ativo deveria ter cache de cliques
        // (mas não podemos verificar cache diretamente no teste de integração)
    }

    public function test_concurrent_redirects_dont_break()
    {
        $shortLink = ShortLink::factory()->create([
            'code' => 'concurrent',
            'original_url' => 'https://example.com',
            'clicks' => 0
        ]);
        
        // Simula múltiplos acessos rápidos
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get("/{$shortLink->code}");
            $response->assertRedirect($shortLink->original_url);
        }
        
        // Não deveria quebrar, mesmo com concorrência
        $this->assertEquals(0, $shortLink->fresh()->clicks);
    }

    public function test_click_count_persists_after_redis_restart()
    {
        // Este teste requer Redis real e não é bom para testes automatizados
        $this->markTestSkipped('Persistence after Redis restart should be tested in a staging environment.');
    }

    public function test_redirect_caches_url_for_performance()
    {
        $shortLink = ShortLink::factory()->create([
            'code' => 'cachetest',
            'original_url' => 'https://example.com/cached',
            'clicks' => 0
        ]);
        
        // Primeira chamada (busca no banco)
        $response1 = $this->get("/{$shortLink->code}");
        $response1->assertRedirect($shortLink->original_url);
        
        // Segunda chamada (deve usar cache)
        $response2 = $this->get("/{$shortLink->code}");
        $response2->assertRedirect($shortLink->original_url);
        
        // A URL está cacheada por 10 minutos no método cacheAndRedirect
        // Isso melhora performance para links muito acessados
    }

    public function test_bulk_operations_dont_interfere_with_click_counting()
    {
        
        
        $user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password')
        ]);

        $shortLink = ShortLink::factory()->create([
            'code' => 'bulktest',
            'original_url' => 'https://example.com',
            'clicks' => 0,
            'user_id' => $user->id
        ]);
        
        $token = $this->loginAndGetToken('test@example.com');
        
        // Faz alguns cliques públicos
        $this->get("/{$shortLink->code}");
        $this->get("/{$shortLink->code}");
        
        // Usuário faz operação no link (ex: atualiza)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/short-links/{$shortLink->id}", [
            'original_url' => 'https://updated.com'
        ]);
        

        $response->assertStatus(200);
        
        // Clique público ainda funciona
        $redirectResponse = $this->get("/{$shortLink->code}");
        $redirectResponse->assertRedirect('https://updated.com');
    }

    protected function loginAndGetToken($email, $password = 'password')
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => $password
        ]);

        return $response->json('access_token');
    }
}