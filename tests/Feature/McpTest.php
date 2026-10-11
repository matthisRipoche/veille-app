<?php

namespace Tests\Feature;

use App\Mcp\Servers\VeilleServer;
use App\Mcp\Tools\GetReportTool;
use App\Mcp\Tools\ListReportsTool;
use App\Mcp\Tools\SaveReportTool;
use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['veille.api_token' => 'secret', 'veille.site_password' => 'ignore-par-mcp']);
    }

    public function test_save_report_cree_puis_remplace(): void
    {
        VeilleServer::tool(SaveReportTool::class, [
            'date' => '2026-10-11',
            'title' => 'Bilan',
            'content_md' => '## IA',
        ])->assertOk()->assertSee('créé');

        VeilleServer::tool(SaveReportTool::class, [
            'date' => '2026-10-11',
            'title' => 'Bilan v2',
            'content_md' => '## IA v2',
        ])->assertOk()->assertSee('mis à jour');

        $this->assertDatabaseCount('reports', 1);
        $this->assertSame('Bilan v2', Report::first()->title);
    }

    public function test_save_report_refuse_une_date_invalide(): void
    {
        VeilleServer::tool(SaveReportTool::class, [
            'date' => '2026-02-30',
            'title' => 'Bilan',
            'content_md' => '## IA',
        ])->assertHasErrors();

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_list_et_get(): void
    {
        Report::create(['date' => '2026-10-11', 'title' => 'Bilan', 'summary' => '', 'content_md' => '## IA']);

        VeilleServer::tool(ListReportsTool::class, [])->assertOk()->assertSee('2026-10-11');
        VeilleServer::tool(GetReportTool::class, ['date' => '2026-10-11'])->assertOk()->assertSee('## IA');
        VeilleServer::tool(GetReportTool::class, ['date' => '2026-10-12'])->assertHasErrors();
    }

    public function test_endpoint_http_exige_le_jeton(): void
    {
        $init = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2025-06-18',
                'capabilities' => (object) [],
                'clientInfo' => ['name' => 'test', 'version' => '1.0'],
            ],
        ];

        $this->postJson('/mcp/veille', $init)->assertStatus(401);

        $this->withToken('secret')->postJson('/mcp/veille', $init)
            ->assertOk()
            ->assertJsonPath('result.serverInfo.name', 'Veille');
    }
}
