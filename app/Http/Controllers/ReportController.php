<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Support\Markdown;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /** Accueil : les 7 derniers rapports. */
    public function home(): View
    {
        $reports = Report::query()
            ->orderByDesc('date')
            ->limit(config('veille.home_days'))
            ->get(['id', 'date', 'title', 'summary']);

        return view('home', [
            'reports' => $reports,
            'total' => Report::count(),
        ]);
    }

    /** Liste paginée, du plus récent au plus ancien. */
    public function index(Request $request): View
    {
        $size = (int) config('veille.page_size');
        $total = Report::count();
        $pages = max(1, (int) ceil($total / $size));
        $page = min(max((int) $request->query('page', 1), 1), $pages);

        $reports = Report::query()
            ->orderByDesc('date')
            ->paginate($size, ['id', 'date', 'title', 'summary'], 'page', $page)
            ->withPath(route('reports.index', [], false));

        return view('reports.index', [
            'reports' => $reports,
            'total' => $total,
        ]);
    }

    /** Détail d'un rapport. */
    public function show(string $date): View
    {
        abort_unless($this->isValidDate($date), 404);

        $report = Report::query()->whereDate('date', $date)->firstOrFail();

        return view('reports.show', [
            'report' => $report,
            'html' => Markdown::toHtml($report->content_md),
            'prev' => Report::query()->where('date', '<', $date)->orderByDesc('date')->first(['id', 'date', 'title']),
            'next' => Report::query()->where('date', '>', $date)->orderBy('date')->first(['id', 'date', 'title']),
        ]);
    }

    private function isValidDate(string $date): bool
    {
        return Carbon::hasFormat($date, 'Y-m-d') && Carbon::createFromFormat('Y-m-d', $date)->toDateString() === $date;
    }
}
