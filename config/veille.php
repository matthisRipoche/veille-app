<?php

return [

    /*
    | Jeton exigé pour déposer un rapport (API REST et serveur MCP HTTP).
    */
    'api_token' => env('API_TOKEN', ''),

    /*
    | Mot de passe optionnel qui protège la lecture du site (HTTP Basic,
    | nom d'utilisateur libre). Vide = site public.
    */
    'site_password' => env('SITE_PASSWORD', ''),

    'page_size' => 10,
    'home_days' => 7,

];
