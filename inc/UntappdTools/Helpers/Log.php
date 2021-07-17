<?php

declare(strict_types=1);

namespace UntappdTools\Helpers;

class Log
{
    private array $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function write(string $data = '', string $type = 'INFO')
    {
        if (!empty($data)) {
            file_put_contents($this->options['log_file_path'], $type.' - '.date('d.m.Y H:i').' - '.$data."\r\n", FILE_APPEND);
        }
    }

    public function error(string $data = '')
    {
        $this->write($data, 'ERROR');
    }
}