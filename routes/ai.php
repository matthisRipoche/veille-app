<?php

use App\Mcp\Servers\VeilleServer;
use Laravel\Mcp\Facades\Mcp;

// Serveur MCP HTTP : POST /mcp/veille avec « Authorization: Bearer <API_TOKEN> ».
Mcp::web('/mcp/veille', VeilleServer::class)->middleware('veille.token');

// Serveur MCP local (stdio) : `php artisan mcp:start veille`, sans jeton.
Mcp::local('veille', VeilleServer::class);
