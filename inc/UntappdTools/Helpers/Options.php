<?php

declare(strict_types=1);

namespace UntappdTools\Helpers;

class Options
{
    const OPTIONS_LIST = [
        'brewery_id', 'client_id', 'client_secret', 'access_token', 'log_file_path'
    ];

    const OPTIONS_NAME = 'untappd_tools';

    public function __construct()
    {
        return self::load();
    }

    public function load(): array
    {
        return get_option(self::OPTIONS_NAME);
    }
}