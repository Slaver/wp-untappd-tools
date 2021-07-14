<?php

declare(strict_types=1);

namespace UntappdTools\Api;

use UntappdTools;
use UntappdTools\Api;
use UntappdPHP;

class LoadCheckins extends Api
{
    public function run()
    {
        $untappd = new UntappdPHP(
            $this->options['client_id'],
            $this->options['client_secret'], ''
        );
        $data = $untappd->get("/brewery/checkins/".$this->options['brewery_id']."?compact=false");

        if (!empty($data->response->checkins)) {
            $beers = $this->posts->beers();
            foreach ($data->response->checkins->items as $checkin) {
                if (!empty($beers[$checkin->beer->bid])) {
                    $exists_checkin = $this->checkins->getCheckin($checkin->checkin_id);

                    $fileds = [
                        'post_id' => $beers[$checkin->beer->bid],
                        'checkin_beer_id' => $checkin->beer->bid,
                        'checkin_rating' => $checkin->rating_score,
                        'checkin_comment' => ($checkin->checkin_comment) ?: NULL,
                        'checkin_venue_id' => (!empty($checkin->venue->venue_id)) ? $checkin->venue->venue_id : NULL,
                        'checkin_media' => ($checkin->media->count > 0) ? json_encode($checkin->media) : NULL,
                        'checkin_retail_venue_id' => (!empty($checkin->retail_venue->venue_id)) ? $checkin->retail_venue->venue_id : NULL,
                    ];

                    if (!empty($checkin->venue->venue_id)) {
                        $exists_venue = $this->checkins->getVenue($checkin->venue->venue_id);

                        if (!$exists_venue) {
                            $this->checkins->addVenue([
                                'venue_id' => $checkin->venue->venue_id,
                                'venue_name' => $checkin->venue->venue_name,
                                'venue_city' => (!empty($checkin->venue->location->venue_city)) ? $checkin->venue->location->venue_city : NULL,
                                'venue_country' => (!empty($checkin->venue->location->venue_country)) ? $checkin->venue->location->venue_country : NULL,
                                'venue_data' => (!empty($checkin->venue)) ? json_encode($checkin->venue) : NULL,
                            ]);
                            $this->log->write('Insert venue: '.$checkin->checkin_id);
                        }
                    }

                    if (!empty($checkin->retail_venue->venue_id)) {
                        $exists_retail_venue = $this->checkins->getVenue($checkin->retail_venue->venue_id);

                        if (!$exists_retail_venue) {
                            $this->checkins->addVenue([
                                'venue_id' => $checkin->retail_venue->venue_id,
                                'venue_name' => $checkin->retail_venue->venue_name,
                                'venue_city' => (!empty($checkin->retail_venue->location->venue_city)) ? $checkin->retail_venue->location->venue_city : NULL,
                                'venue_country' => (!empty($checkin->retail_venue->location->venue_country)) ? $checkin->retail_venue->location->venue_country : NULL,
                                'venue_data' => (!empty($checkin->retail_venue)) ? json_encode($checkin->retail_venue) : NULL,
                            ]);
                            $this->log->write('Insert retail: '.$checkin->checkin_id);
                        }
                    }

                    if (!$exists_checkin) {
                        $insert = [
                            'added_date' => date('Y-m-d H:i:s', time()),
                            'checkin_id' => $checkin->checkin_id,
                            'checkin_time' => date('Y-m-d H:i:s', strtotime($checkin->created_at)),
                            'checkin_user_id' => $checkin->user->uid,
                            'checkin_user_data' => json_encode($checkin->user),
                        ];
                        $this->checkins->addCheckin(array_merge($insert, $fileds));
                        $this->log->write('New checkin: '.$checkin->checkin_id);
                    } else {
                        if ($exists_checkin->checkin_rating != $checkin->rating_score || $exists_checkin->checkin_comment != $checkin->checkin_comment) {
                            $update = [
                                'updated_date' => date('Y-m-d H:i:s', time()),
                                'updated_data' => json_encode([
                                    'checkin_rating' => $exists_checkin->checkin_rating,
                                    'checkin_comment' => $exists_checkin->checkin_comment,
                                ]),
                            ];
                            $this->checkins->updateCheckin((int)$exists_checkin->id, array_merge($update, $fileds));
                            $this->log->write('Update checkin: '.$checkin->checkin_id);
                        }
                    }
                }
            }
        } else {
            $this->log->error($data->meta->error_detail);
        }
    }
}