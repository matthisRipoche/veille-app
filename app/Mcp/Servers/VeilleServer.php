<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetReportTool;
use App\Mcp\Tools\ListReportsTool;
use App\Mcp\Tools\SaveReportTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Veille')]
#[Version('1.0.0')]
#[Instructions(<<<'TXT'
Archive des bilans quotidiens de veille technologique.

Pour publier un bilan, appelle `save_report` avec :
- `date` : jour du bilan, au format AAAA-MM-JJ ;
- `title` : titre court (200 caractères max) ;
- `summary` : résumé en une ou deux phrases (optionnel, 1000 caractères max) ;
- `content_md` : contenu en Markdown (titres `##` par thème, listes à puces, sources en liens `[nom](https://...)`). Le HTML brut est supprimé.

Il n'y a qu'un rapport par date : rappeler `save_report` avec la même date remplace le rapport existant. Utilise `list_reports` ou `get_report` pour vérifier ce qui existe déjà avant d'écraser un bilan.
TXT)]
class VeilleServer extends Server
{
    protected array $tools = [
        SaveReportTool::class,
        ListReportsTool::class,
        GetReportTool::class,
    ];
}
