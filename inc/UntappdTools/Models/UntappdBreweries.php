<?php

declare(strict_types=1);

namespace UntappdTools\Models;

use UntappdTools\Models;

class UntappdBreweries extends Models
{
    public function __construct() {
        parent::__construct();

        $this->table = $this->wpdb->prefix.'untappd_breweries';
    }

    public function getBreweries(): ?array
    {
        return $this->wpdb->get_results("SELECT LOWER(title) as title_key, title, id, url FROM $this->table", OBJECT_K);
    }

    public function addBrewery(array $data = []): int
    {
        $this->wpdb->insert($this->table, $data);
        return $this->wpdb->insert_id;
    }
}