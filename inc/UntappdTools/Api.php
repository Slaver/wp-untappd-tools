<?php

declare(strict_types=1);

namespace UntappdTools;

use UntappdTools\Helpers;

class Api
{
    protected array $options;
    protected Helpers\Log $log;
    protected Models\Posts $posts;
    protected Models\UntappdBeers $beers;
    protected Models\UntappdBeersRating $beersRating;
    protected Models\UntappdCheckins $checkins;
    public Models\UntappdBreweries $breweries;
    public Models\UntappdBreweriesRating $breweriesRatings;

    public function __construct($options)
    {
        $this->options = $options;
        $this->log = new Helpers\Log($this->options);
        $this->posts = new Models\Posts();
        $this->beers = new Models\UntappdBeers();
        $this->beersRating = new Models\UntappdBeersRating();
        $this->checkins = new Models\UntappdCheckins();
        $this->breweries = new Models\UntappdBreweries();
        $this->breweriesRatings = new Models\UntappdBreweriesRating();
    }
}