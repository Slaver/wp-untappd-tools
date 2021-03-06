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
        $return = [];
        $result = $this->wpdb->get_results("SELECT title, id, url FROM $this->table", ARRAY_A);
        if ($result) {
            foreach ($result as $row) {
                $return[mb_strtolower($row['title'])] = $row;
            }
        }
        return $return;
    }

    public function addBrewery(array $data = []): int
    {
        $this->wpdb->insert($this->table, $data);
        return $this->wpdb->insert_id;
    }
}