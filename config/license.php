<?php

return [
    /*
    | License of this installation (set by the /license-setup page).
    | Read through config (not env() in the code) so it also works when the
    | config is cached (php artisan config:cache).
    */
    'key' => env('LICENSE_KEY', ''),

    /*
    | License server. Empty = https://travelbookingpanel.com. A localhost
    | address is only used when this site itself runs on localhost.
    */
    'server_url' => env('LICENSE_SERVER_URL', ''),
];
