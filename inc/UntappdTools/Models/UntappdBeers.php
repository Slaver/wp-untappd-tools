<?php

declare(strict_types=1);

namespace UntappdTools\Models;

use UntappdTools\Models;

class UntappdBeers extends Models
{
    public function __construct() {
        parent::__construct();

        $this->table = $this->wpdb->prefix.'untappd_beers';
    }

    public function getBeerByUntappdId(int $id): ?object
    {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table WHERE untappd_id = %d", $id));
    }

    public function addBeer(array $data = []): int
    {
        $this->wpdb->insert($this->table, $data);
        return $this->wpdb->insert_id;
    }

    public function updateBeer(int $id, array $data = [])
    {
        $this->wpdb->update($this->table, $data, ['id' => $id]);
    }
}