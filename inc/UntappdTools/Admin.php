<?php

declare(strict_types=1);

namespace UntappdTools;

use UntappdTools\Models;

class Admin
{
    public static function page()
    {
        $usersPopular = $usersLoyal = $usersHaters = [];
        $checkins = new Models\UntappdCheckins();

        // Checkins stats
        $checkinsTotal = $checkins->totalCheckins();
        $checkinsDays7 = $checkins->totalCheckins(7);
        $checkinsDays30 = $checkins->totalCheckins(30);
        $ratingTotal = $checkins->totalRatings();
        $ratingDays7 = $checkins->totalRatings(7);
        $ratingDays30 = $checkins->totalRatings(30);

        // Users stats
        $usersPopular = $checkins->getPopularUsers();
        $usersLoyal = $checkins->getLoyalUsers();
        $usersHaters = $checkins->getHatersUsers();

        $users = $checkins->getUsersById(array_unique(array_merge(
            array_keys($usersPopular),
            array_keys($usersLoyal),
            array_keys($usersHaters)
        )));

        foreach ($users as $user_id => $user_data) {
            $data = json_decode($user_data->checkin_user_data);
            if (!empty($usersPopular[$user_id])) {
                $usersPopular[$user_id]['name'] = $data->user_name;
            }
            if (!empty($usersLoyal[$user_id])) {
                $usersLoyal[$user_id]['name'] = $data->user_name;
            }
            if (!empty($usersHaters[$user_id])) {
                $usersHaters[$user_id]['name'] = $data->user_name;
            }
        }

        // Places stats
        $places = $checkins->getVenues();

        $placesPopular = $checkins->getPopularVenues();
        $placesPopularDays30 = $checkins->getPopularVenues(30);
        $placesPopularPurchased = $checkins->getPopularPurchasedVenues();
        $placesPopularPurchasedDays30 = $checkins->getPopularPurchasedVenues(30);
        $placesLoyal = $checkins->getLoyalVenues();
        $placesLoyalDays30 = $checkins->getLoyalVenues(30);

        foreach ($places as $place) {
            if (!empty($placesPopular[$place->venue_id])) {
                $placesPopular[$place->venue_id]['name'] = $place->venue_name;
            }
            if (!empty($placesPopularDays30[$place->venue_id])) {
                $placesPopularDays30[$place->venue_id]['name'] = $place->venue_name;
            }
            if (!empty($placesLoyal[$place->venue_id])) {
                $placesLoyal[$place->venue_id]['name'] = $place->venue_name;
            }
            if (!empty($placesLoyalDays30[$place->venue_id])) {
                $placesLoyalDays30[$place->venue_id]['name'] = $place->venue_name;
            }
            if (!empty($placesPopularPurchased[$place->venue_id])) {
                $placesPopularPurchased[$place->venue_id]['name'] = $place->venue_name;
            }
            if (!empty($placesPopularPurchasedDays30[$place->venue_id])) {
                $placesPopularPurchasedDays30[$place->venue_id]['name'] = $place->venue_name;
            }
        }

        include UT_DIR . 'templates/admin.php';
    }
}