<?php

declare(strict_types=1);

namespace UntappdTools\Models;

use UntappdTools\Models;

class UntappdBreweriesRating extends Models
{
    public function __construct() {
        parent::__construct();

        $this->table = $this->wpdb->prefix.'untappd_breweries_ratings';
    }

    public function lastUpdate(): ?string
    {
        return $this->wpdb->get_var("SELECT MAX(date_added) FROM $this->table");
    }

    public function getRating(int $brewery_id): ?array
    {
        return $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM $this->table WHERE brewery_id=%d ORDER BY id DESC LIMIT %d", $brewery_id, 1
        ), ARRAY_A);
    }

    public function addRating(array $data = [])
    {
        $this->wpdb->insert($this->table, $data);
    }
}