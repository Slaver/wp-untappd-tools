<?php

declare(strict_types=1);

namespace UntappdTools;

use DateTime;

class Cron
{
    const RATING_UPDATE_GAP = HOUR_IN_SECONDS * 20;
    const RETAIL_UPDATE_GAP = HOUR_IN_SECONDS * 2;

    private array $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function ratings()
    {
        $api = new Api\LoadRatings($this->options);
        $date_now = new DateTime();
        $last_update = $api->breweriesRatings->lastUpdate();

        // First run
        if (!$last_update) {
            $date_update = new \DateTime();
            $date_update->modify('-'.self::RATING_UPDATE_GAP.' seconds');
        } else {
            $date_update = new \DateTime($last_update);
            $date_update->modify('+'.self::RATING_UPDATE_GAP.' seconds');
        }

        if ($date_update && $date_update < $date_now) {
            $api->run();
        }
    }

    public function checkins()
    {
        $api = new Api\LoadCheckins($this->options);
        $api->run();
    }

    public function toast()
    {
        $api = new Api\ToastCheckins($this->options);
        $api->run();
    }

    public function retail()
    {
        $api = new Api\CheckRetail($this->options);
        $api->run(self::RETAIL_UPDATE_GAP);
    }

    public function beers()
    {
        $api = new Api\LoadBeersStats($this->options);
        $api->run();
    }
}
