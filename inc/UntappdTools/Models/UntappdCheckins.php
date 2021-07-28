<?php

declare(strict_types=1);

namespace UntappdTools\Models;

use UntappdTools\Models;

class UntappdCheckins extends Models
{
    private string $venues = '';

    public function __construct() {
        parent::__construct();

        $this->table = $this->wpdb->prefix.'untappd_checkins';
        $this->tableVenues = $this->wpdb->prefix.'untappd_venues';
    }

    public function totalCheckins(int $days = 0): ?string
    {
        $and = ($days) ? $this->wpdb->prepare(" WHERE checkin_time BETWEEN NOW() - INTERVAL %d DAY AND NOW()", $days) : '';
        return $this->wpdb->get_var("SELECT count(id) from $this->table".$and);
    }

    public function totalRatings(int $days = 0, int $minRating = 0): ?string
    {
        $and = ($days) ? $this->wpdb->prepare("AND checkin_time BETWEEN NOW() - INTERVAL %d DAY AND NOW()", $days) : '';
        return $this->wpdb->get_var( $this->wpdb->prepare("SELECT avg(checkin_rating) FROM $this->table WHERE checkin_rating > %d $and", $minRating));
    }

    public function getUsersById(array $userIds): ?array
    {
        $list = implode( ',', $userIds);
        return $this->wpdb->get_results("SELECT checkin_user_id, checkin_user_data FROM $this->table WHERE checkin_user_id IN ($list)", OBJECT_K);
    }

    public function getPopularUsers(int $limit = 20): ?array
    {
        $return = [];
        $result = $this->wpdb->get_results( $this->wpdb->prepare("SELECT checkin_user_id, count(*) as counter FROM $this->table
            GROUP BY checkin_user_id
            ORDER BY count(*) DESC, checkin_user_id DESC LIMIT %d", $limit));

        foreach ($result as $user) {
            $return[$user->checkin_user_id]['counter'] = $user->counter;
        }
        return $return;
    }

    public function getLoyalUsers(int $limit = 40, int $minCheckins = 10): ?array
    {
        $return = [];
        $result = $this->wpdb->get_results( $this->wpdb->prepare("SELECT checkin_user_id, AVG(checkin_rating) as average, COUNT(checkin_rating) as counter
            FROM $this->table
            WHERE checkin_rating > %d
            GROUP BY checkin_user_id
            HAVING COUNT(checkin_rating) > %d
            ORDER BY AVG(checkin_rating) DESC, counter DESC
        LIMIT %d", 0, $minCheckins, $limit));

        foreach ($result as $user) {
            $return[$user->checkin_user_id]['average'] = $user->average;
            $return[$user->checkin_user_id]['counter'] = $user->counter;
        }

        return $return;
    }

    public function getHatersUsers(int $limit = 20, int $minCheckins = 5): ?array
    {
        $return = [];
        $result = $this->wpdb->get_results($this->wpdb->prepare("SELECT checkin_user_id, AVG(checkin_rating) as average, COUNT(checkin_rating) as counter
            FROM $this->table
            WHERE checkin_rating > %d
            GROUP BY checkin_user_id
            HAVING COUNT(checkin_rating) > %d
            ORDER BY AVG(checkin_rating) ASC, counter DESC
        LIMIT %d", 0, $minCheckins, $limit));

        foreach ($result as $user) {
            $return[$user->checkin_user_id]['average'] = $user->average;
            $return[$user->checkin_user_id]['counter'] = $user->counter;
        }

        return $return;
    }

    public function getVenues(): ?array
    {
        return $this->wpdb->get_results("SELECT venue_id, venue_name, venue_city, venue_country, venue_data FROM $this->tableVenues", OBJECT_K);
    }

    public function getPopularVenues(int $days = 0, int $limit = 20): ?array
    {
        $return = [];
        $and = ( $days ) ? $this->wpdb->prepare(" AND checkin_time BETWEEN NOW() - INTERVAL %d DAY AND NOW()", $days) : '';
        $result = $this->wpdb->get_results($this->wpdb->prepare( "SELECT checkin_venue_id, count(*) as counter 
            FROM $this->table
            WHERE checkin_venue_id IS NOT NULL $and GROUP BY checkin_venue_id ORDER BY count(*) DESC LIMIT %d", $limit));

        foreach ($result as $place) {
            $return[$place->checkin_venue_id]['counter'] = $place->counter;
        }

        return $return;
    }

    public function getLoyalVenues(int $days = 0, int $limit = 20, int $minRating = 50): ?array
    {
        $return = [];
        $and = ( $days ) ? $this->wpdb->prepare(" AND checkin_time BETWEEN NOW() - INTERVAL %d DAY AND NOW()", $days) : '';
        $result = $this->wpdb->get_results($this->wpdb->prepare("SELECT checkin_venue_id, AVG(checkin_rating) as average, COUNT(checkin_rating) as counter
            FROM $this->table
            WHERE checkin_rating > %d AND checkin_venue_id IS NOT NULL $and
            GROUP BY checkin_venue_id
            HAVING COUNT(checkin_rating) > %d
            ORDER BY AVG(checkin_rating) DESC, counter DESC LIMIT %d", 0, $minRating, $limit));

        foreach ($result as $place) {
            $return[$place->checkin_venue_id]['average'] = $place->average;
            $return[$place->checkin_venue_id]['counter'] = $place->counter;
        }

        return $return;

    }

    public function getPopularPurchasedVenues(int $days = 0, int $limit = 20): ?array
    {
        $return = [];
        $and = ( $days ) ? $this->wpdb->prepare(" AND checkin_time BETWEEN NOW() - INTERVAL %d DAY AND NOW()", $days) : '';
        $result = $this->wpdb->get_results( $this->wpdb->prepare("SELECT checkin_retail_venue_id, count(*) as counter 
            FROM $this->table
            WHERE checkin_retail_venue_id IS NOT NULL $and
            GROUP BY checkin_retail_venue_id 
            ORDER BY count(*) DESC LIMIT %d", $limit));

        foreach ($result as $place) {
            $return[$place->checkin_retail_venue_id]['counter'] = $place->counter;
        }

        return $return;
    }

    public function getCheckinsForToast(int $limit = 20, int $minRating = 4, int $days = 10): ?array
    {
        return $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM $this->table
            WHERE is_toasted = %d AND is_deleted = %d AND checkin_rating >= %d AND checkin_time >= ( CURDATE() - INTERVAL %d DAY )
            ORDER BY checkin_time DESC LIMIT %d", 0, 0, $minRating, $days, $limit));
    }

    public function getCheckin(int $id): ?object
    {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table WHERE checkin_id = %d", $id));
    }

    public function getVenue(int $id): ?object
    {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->tableVenues WHERE venue_id = %d", $id));
    }

    public function addVenue(array $data = [])
    {
        $this->wpdb->insert($this->tableVenues, $data);
    }

    public function addCheckin(array $data = [])
    {
        $this->wpdb->insert($this->table, $data);
    }

    public function updateCheckin(int $id, array $data = [])
    {
        $this->wpdb->update($this->table, $data, ['id' => $id]);
    }
}