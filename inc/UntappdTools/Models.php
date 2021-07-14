<?php

namespace UntappdTools;

class Models
{
    protected $wpdb = false;
    protected string $table = '';

    public function __construct()
    {
        global $wpdb;

        if (is_object($wpdb)) {
            $this->wpdb = $wpdb;
        }
    }
}