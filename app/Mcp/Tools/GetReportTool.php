<?php

namespace App\Mcp\Tools;

use App\Models\Report;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('get_report')]
#[Description('Renvoie le bilan d\'une date (titre, résumé et contenu Markdown), ou une erreur s\'il n\'existe pas.')]
class GetReportTool extends Tool
{
    public function schema(JsonSchema $schema): array
    {
        return [
            'date' => $schema->string()
                ->description('Date du bilan au format AAAA-MM-JJ.')
                ->required(),
        ];
    }

    public function handle(Request $request): Response
    {
        $data = $request->validate(['date' => ['required', 'string', 'date_format:Y-m-d']]);

        $report = Report::query()->whereDate('date', $data['date'])->first();

        if (! $report) {
            return Response::error("Aucun rapport pour la date {$data['date']}.");
        }

        return Response::json([
            'date' => $report->slug(),
            'title' => $report->title,
            'summary' => $report->summary,
            'content_md' => $report->content_md,
            'url' => route('reports.show', $report->slug()),
        ]);
    }
}
