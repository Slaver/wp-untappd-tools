<?php

declare(strict_types=1);

namespace UntappdTools\Api;

use UntappdTools;
use UntappdTools\Api;
use PHPHtmlParser\Dom;

class LoadRatings extends Api
{
    const PARSE_URLS = [
        'https://untappd.com/brewery/top_rated?country=belarus',
        'https://untappd.com/brewery/top_rated?country=russia',
        'https://untappd.com/brewery/top_rated?country=ukraine',
        'https://untappd.com/brewery/top_rated?country=poland',
        'https://untappd.com/brewery/top_rated?country=lithuania',
    ];

    const BREWERY_TYPES = [
        1 => 'Macro Brewery',
        2 => 'Micro Brewery',
        3 => 'Nano Brewery',
        4 => 'Brew Pub',
        8 => 'Cidery',
        9 => 'Meadery',
        10 => 'Contract Brewery', // Contractbrouwerij
        11 => 'Regional Brewery',
    ];

    public function run()
    {
        $breweries = $this->breweries->getBreweries();
        $report = [];

        foreach (self::PARSE_URLS as $parse_url) {
            $dom = new Dom;
            $dom->loadFromUrl($parse_url);
            $contents = $dom->find('.beer-item');

            if (count($contents)) {
                $i = 1;
                foreach ($contents as $content) {
                    $brewery = trim($content->find('.name a')->text);
                    $breweryKey = mb_strtolower($brewery);
                    $beers = preg_replace('/\D/', '', $content->find('.details.brewery .abv')->text);
                    $ratings = preg_replace('/\D/', '', $content->find('.details.brewery .ibu')->text);
                    $rating = $content->find('.caps')->getAttribute('data-rating');
                    $location = explode(' ', $content->find('.beer-details .style')[0]->text);
                    $country = end($location);

                    if (!is_string($brewery) || !is_numeric($beers) || !is_numeric($ratings)) {
                        $this->log->error('Couldn\'t parse rating '.$parse_url);
                    }

                    $breweryId = NULL;
                    if (!empty($breweries[$breweryKey])) {
                        $breweryId = (int)$breweries[$breweryKey]->id;
                    } else {
                        $url = $content->find('a.label')[0]->href;
                        $type = $content->find('.beer-details .style')[1]->text;
                        if ($type == 'Contractbrouwerij') {
                            $type = 'Contract Brewery';
                        }

                        if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url)) {
                            $url = 'https://untappd.com'.$url;
                        }

                        $breweries[$breweryKey] = [
                            'title' => $brewery,
                            'url'   => $url,
                            'type'  => array_search($type, self::BREWERY_TYPES) ?: NULL,
                            'country' => $country,
                        ];

                        $breweryId = $this->breweries->addBrewery($breweries[$breweryKey]);
                    }

                    if ($breweryId) {
                        $brewery_yesterday = $this->breweriesRatings->getRating($breweryId);

                        $this->breweriesRatings->addRating([
                            'rating_place' => $i,
                            'brewery_id' => $breweryId,
                            'beers'   => $beers,
                            'ratings' => $ratings,
                            'rating'  => $rating,
                            'country' => $country,
                        ]);

                        $report[$country][$breweryId] = [
                            'rating_place' => $i,
                            'brewery' => $breweries[$breweryKey],
                            'beers'   => $beers,
                            'ratings' => $ratings,
                            'rating'  => $rating,
                        ];

                        if (!empty($brewery_yesterday)) {
                            $report[$country][$breweryId]['yesterday'] = $brewery_yesterday;
                        }

                        $i++;
                    } else {
                        $this->log->error('Rating brewery '.$brewery.' not found');
                    }
                }
            } else {
                $this->log->error('Couldn\'t fetch rating '.$parse_url);
            }
        }

        ob_start();
        include UT_DIR.'templates/mails/rating.php';
        $email_content = ob_get_contents();
        ob_end_clean();
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail(get_option('admin_email'), "Rating update", $email_content, $headers);
        $this->log->write('Breweries rating updated successfuly');
    }
}