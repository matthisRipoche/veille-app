<?php

namespace Tests\Feature;

use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'date' => '2026-10-11',
        'title' => 'Bilan de veille du 11 octobre 2026',
        'summary' => 'Un résumé.',
        'content_md' => "## IA\n\n- Point [source](https://exemple.com)\n",
    ];

    protected function setUp(): void
    {
        parent::setUp();
        config(['veille.api_token' => 'secret', 'veille.site_password' => '']);
    }

    public function test_refuse_sans_jeton_ou_avec_mauvais_jeton(): void
    {
        $this->postJson('/api/reports', $this->payload)->assertStatus(401);
        $this->withToken('autre')->postJson('/api/reports', $this->payload)->assertStatus(401);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_cree_puis_remplace_un_rapport(): void
    {
        $this->withToken('secret')->postJson('/api/reports', $this->payload)
            ->assertStatus(201)
            ->assertJson(['ok' => true, 'date' => '2026-10-11', 'created' => true]);

        $this->withToken('secret')->postJson('/api/reports', [...$this->payload, 'title' => 'Nouveau titre'])
            ->assertStatus(200)
            ->assertJson(['ok' => true, 'created' => false]);

        $this->assertDatabaseCount('reports', 1);
        $this->assertSame('Nouveau titre', Report::first()->title);
    }

    public function test_summary_est_optionnel(): void
    {
        $payload = $this->payload;
        unset($payload['summary']);

        $this->withToken('secret')->postJson('/api/reports', $payload)->assertStatus(201);
        $this->assertSame('', Report::first()->summary);
    }

    public function test_donnees_invalides_renvoient_400(): void
    {
        foreach ([
            ['date' => '2026-02-30'],
            ['date' => '11/10/2026'],
            ['title' => '   '],
            ['content_md' => ''],
            ['title' => str_repeat('a', 201)],
        ] as $override) {
            $this->withToken('secret')->postJson('/api/reports', [...$this->payload, ...$override])
                ->assertStatus(400)
                ->assertJsonStructure(['error']);
        }

        $this->assertDatabaseCount('reports', 0);
    }
}
