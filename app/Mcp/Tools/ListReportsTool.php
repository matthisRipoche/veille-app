<?php

namespace App\Mcp\Tools;

use App\Models\Report;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('list_reports')]
#[Description('Liste les derniers bilans (date, titre, résumé), du plus récent au plus ancien.')]
class ListReportsTool extends Tool
{
    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()
                ->description('Nombre de bilans à renvoyer, entre 1 et 50 (10 par défaut).')
                ->min(1)
                ->max(50),
        ];
    }

    public function handle(Request $request): Response
    {
        $data = $request->validate(['limit' => ['nullable', 'integer', 'min:1', 'max:50']]);

        $reports = Report::query()
            ->orderByDesc('date')
            ->limit($data['limit'] ?? 10)
            ->get(['date', 'title', 'summary'])
            ->map(fn (Report $r) => [
                'date' => $r->slug(),
                'title' => $r->title,
                'summary' => $r->summary,
            ])
            ->all();

        return Response::json(['total' => Report::count(), 'reports' => $reports]);
    }
}
