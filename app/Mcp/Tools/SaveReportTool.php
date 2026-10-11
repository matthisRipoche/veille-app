<?php

namespace App\Mcp\Tools;

use App\Actions\SaveReport;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('save_report')]
#[Description('Crée le bilan de veille d\'une date, ou le remplace s\'il existe déjà (un seul rapport par date).')]
class SaveReportTool extends Tool
{
    public function schema(JsonSchema $schema): array
    {
        return [
            'date' => $schema->string()
                ->description('Date du bilan au format AAAA-MM-JJ, par exemple 2026-10-11.')
                ->required(),
            'title' => $schema->string()
                ->description('Titre du bilan, 200 caractères maximum.')
                ->required(),
            'summary' => $schema->string()
                ->description('Résumé court en une ou deux phrases, 1000 caractères maximum. Optionnel.'),
            'content_md' => $schema->string()
                ->description('Contenu du bilan en Markdown : sections `##`, listes à puces, sources en liens [nom](https://...).')
                ->required(),
        ];
    }

    public function handle(Request $request, SaveReport $save): Response
    {
        try {
            $result = $save($request->all());
        } catch (ValidationException $e) {
            return Response::error($e->validator->errors()->first());
        }

        return Response::text(sprintf(
            'Rapport du %s %s : %s',
            $result['date'],
            $result['created'] ? 'créé' : 'mis à jour',
            route('reports.show', $result['date']),
        ));
    }
}
