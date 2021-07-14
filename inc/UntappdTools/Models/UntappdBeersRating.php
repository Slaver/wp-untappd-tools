<?php

declare(strict_types=1);

namespace UntappdTools\Models;

use UntappdTools\Models;

class UntappdBeersRating extends Models
{
    public function __construct() {
        parent::__construct();

        $this->table = $this->wpdb->prefix.'untappd_beers_ratings';
    }

    public function addRating(array $data = [])
    {
        $this->wpdb->insert($this->table, $data);
    }
}