<?php

declare(strict_types=1);

namespace UntappdTools\Api;

use DateTime;
use Exception;
use UntappdPHP;
use UntappdTools\Api;

class LoadBeersStats extends Api
{
    public function run()
    {
        $beers = $this->posts->beers();

        $untappd = new UntappdPHP(
            $this->options['client_id'],
            $this->options['client_secret'], ''
        );

        $dateNow = new DateTime();

        foreach ($beers as $untappdBeerId => $postId) {
            $beerLatestCheckinDate = $beerLatestUpdate = $beerAvailable = false;
            if ($beer = $this->beers->getBeerByUntappdId($untappdBeerId)) {
                $beerId = (int)$beer->id;
                $beerLatestUpdate = $beer->latest_update;
                $beerLatestCheckinDate = $beer->latest_checkin_date;
                $beerAvailable = (bool)get_post_meta($postId, CheckRetail::POST_META_AVAILABLE_FIELD, TRUE);
            } else {
                $beerId = $this->beers->addBeer([
                    'post_id' => $postId,
                    'untappd_id' => $untappdBeerId,
                ]);
            }

            $beerNeedUpdate = false;
            if ($beerAvailable || !$beerLatestCheckinDate || !$beerLatestUpdate) {
                $beerNeedUpdate = true;
            } else {
                try {
                    $dateLatestCheckin = new DateTime($beerLatestCheckinDate);
                    $dateLatestUpdate = new DateTime($beerLatestUpdate);

                    $intervalLatestCheckin = $dateNow->diff($dateLatestCheckin);
                    if ($intervalLatestCheckin->days > 360) {
                        if ($dateNow > $dateLatestUpdate->modify('+30 days')) {
                            $beerNeedUpdate = true;
                        }
                    } elseif ($intervalLatestCheckin->days > 30) {
                        if ($dateNow > $dateLatestUpdate->modify('+7 days')) {
                            $beerNeedUpdate = true;
                        }
                    } else {
                        $beerNeedUpdate = true;
                    }
                } catch (Exception $e) {
                    $this->log->error('Couldn\'t convert date at beer stats update');
                }
            }

            if ($beerNeedUpdate) {
                $beerUpdateData = [];
                $data = $untappd->get('/beer/info/'.$untappdBeerId);

                if (!empty($data->response->beer)) {
                    $this->beersRating->addRating([
                        'beer_id' => $beerId,
                        'rating_count' => $data->response->beer->rating_count,
                        'rating_score' => $data->response->beer->rating_score,
                        'total_count' => $data->response->beer->stats->total_count,
                        'monthly_count' => $data->response->beer->stats->monthly_count,
                        'total_user_count' => $data->response->beer->stats->total_user_count,
                    ]);
                    $beerUpdateData['latest_update'] = $dateNow->format('Y-m-d H:i:s');

                    if (!empty($data->response->beer->checkins->items[0])) {
                        try {
                            $date = new DateTime($data->response->beer->checkins->items[0]->created_at);
                            $beerUpdateData['latest_checkin_date'] = $date->format('Y-m-d H:i:s');
                        } catch (Exception $e) {
                            $this->log->error('Couldn\'t convert date '.$data->response->beer->checkins->items[0]->created_at.' at beer stats '.$untappdBeerId);
                        }
                    }

                    $this->beers->updateBeer($beerId, $beerUpdateData);
                    $this->log->write('Update beer stats: '.$beerId);
                }
            }
        }
    }
}