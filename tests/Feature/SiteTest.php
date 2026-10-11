<?php

namespace Tests\Feature;

use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['veille.site_password' => '']);
    }

    private function report(string $date, array $attrs = []): Report
    {
        return Report::create([
            'date' => $date,
            'title' => "Bilan du $date",
            'summary' => "Résumé $date",
            'content_md' => "## Section\n\n- [lien](https://exemple.com)\n",
            ...$attrs,
        ]);
    }

    public function test_accueil_vide(): void
    {
        $this->get('/')->assertOk()->assertSee('Aucun rapport pour le moment');
    }

    public function test_accueil_affiche_les_7_derniers(): void
    {
        foreach (range(1, 9) as $day) {
            $this->report(sprintf('2026-10-%02d', $day));
        }

        $this->get('/')
            ->assertOk()
            ->assertSee('Bilan du 2026-10-09')
            ->assertSee('Bilan du 2026-10-03')
            ->assertDontSee('Bilan du 2026-10-02')
            ->assertSee('Voir tous les rapports (9)');
    }

    public function test_liste_paginee_du_plus_recent_au_plus_ancien(): void
    {
        foreach (range(1, 12) as $day) {
            $this->report(sprintf('2026-09-%02d', $day));
        }

        $this->get('/reports')
            ->assertOk()
            ->assertSeeInOrder(['Bilan du 2026-09-12', 'Bilan du 2026-09-03'])
            ->assertDontSee('Bilan du 2026-09-02')
            ->assertSee('Page 1 sur 2');

        $this->get('/reports?page=2')->assertOk()->assertSee('Bilan du 2026-09-02')->assertSee('Page 2 sur 2');
        // Page hors limites : ramenée à la dernière page
        $this->get('/reports?page=99')->assertOk()->assertSee('Page 2 sur 2');
    }

    public function test_detail_rend_le_markdown_et_assainit(): void
    {
        $this->report('2026-10-11', [
            'content_md' => "## Titre\n\n<script>alert(1)</script>\n\n[ok](https://exemple.com) [mal](javascript:alert(1))\n",
        ]);

        $response = $this->get('/reports/2026-10-11')->assertOk();
        $response->assertSee('<h2>Titre</h2>', false);
        $response->assertDontSee('<script>', false);
        $response->assertDontSee('javascript:', false);
        $response->assertSee('target="_blank"', false);
        $response->assertSee('noopener', false);
        $response->assertSee('dimanche 11 octobre 2026');
    }

    public function test_navigation_entre_rapports_voisins(): void
    {
        $this->report('2026-10-10');
        $this->report('2026-10-11');
        $this->report('2026-10-12');

        $this->get('/reports/2026-10-11')
            ->assertSee('href="'.url('/reports/2026-10-10').'"', false)
            ->assertSee('href="'.url('/reports/2026-10-12').'"', false);
    }

    public function test_dates_inconnues_ou_invalides_renvoient_404(): void
    {
        $this->get('/reports/2026-10-11')->assertNotFound();
        $this->get('/reports/2026-13-45')->assertNotFound();
        $this->get('/reports/abc')->assertNotFound();
    }

    public function test_en_tetes_de_securite(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_mot_de_passe_du_site(): void
    {
        config(['veille.site_password' => 'sesame']);

        $this->get('/')->assertStatus(401)->assertHeader('WWW-Authenticate');
        $this->get('/', ['Authorization' => 'Basic '.base64_encode('x:mauvais')])->assertStatus(401);
        $this->get('/', ['Authorization' => 'Basic '.base64_encode('nimporte:sesame')])->assertOk();

        $this->get('/healthz')->assertOk();
    }
}
