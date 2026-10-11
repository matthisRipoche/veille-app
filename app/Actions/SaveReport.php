<?php

namespace App\Actions;

use App\Models\Report;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Création ou remplacement d'un rapport (un seul par date).
 * Partagé par l'API REST et les outils MCP pour garder les mêmes règles.
 */
class SaveReport
{
    /**
     * @param  array<string, mixed>  $input
     * @return array{date: string, created: bool}
     *
     * @throws ValidationException
     */
    public function __invoke(array $input): array
    {
        $data = $this->validate($input);

        $report = Report::updateOrCreate(
            ['date' => $data['date']],
            [
                'title' => $data['title'],
                'summary' => $data['summary'],
                'content_md' => $data['content_md'],
            ],
        );

        return ['date' => $report->slug(), 'created' => $report->wasRecentlyCreated];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{date: string, title: string, summary: string, content_md: string}
     *
     * @throws ValidationException
     */
    public function validate(array $input): array
    {
        foreach (['title', 'summary'] as $key) {
            if (isset($input[$key]) && is_string($input[$key])) {
                $input[$key] = trim($input[$key]);
            }
        }

        $validated = Validator::make($input, [
            'date' => ['required', 'string', 'date_format:Y-m-d'],
            'title' => ['required', 'string', 'max:200'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content_md' => ['required', 'string', 'max:1000000'],
        ], [
            'date.required' => 'date est requise (format AAAA-MM-JJ).',
            'date.date_format' => 'date doit être une date valide au format AAAA-MM-JJ.',
            'date.string' => 'date doit être au format AAAA-MM-JJ.',
            'title.required' => 'title est requis (200 caractères max).',
            'title.max' => 'title est requis (200 caractères max).',
            'title.string' => 'title doit être une chaîne de caractères.',
            'summary.max' => 'summary doit faire 1000 caractères max.',
            'summary.string' => 'summary doit être une chaîne de caractères.',
            'content_md.required' => 'content_md est requis (Markdown).',
            'content_md.max' => 'content_md est trop volumineux (1 000 000 caractères max).',
            'content_md.string' => 'content_md doit être une chaîne de caractères (Markdown).',
        ])->validate();

        $validated['summary'] = $validated['summary'] ?? '';

        return $validated;
    }
}
