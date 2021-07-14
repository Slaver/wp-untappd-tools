<?php

declare(strict_types=1);

namespace UntappdTools;

class Hooks
{
    const CRON_GAP = 30 * MINUTE_IN_SECONDS;

    private Cron $cron;

    /**
     * Run WordPress filters and actions
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->cron = new Cron($options);

        add_action('admin_menu', [$this, 'menu']);

        if (WP_PRODUCTION) {
            add_filter('cron_schedules', function ($schedules) {
                if (!isset($schedules['untappd_tools'])) {
                    $schedules['untappd_tools'] = [
                        'interval' => self::CRON_GAP,
                        'display' => __('Every '.self::CRON_GAP.' minutes')
                    ];
                }
                return $schedules;
            });

            if (!wp_next_scheduled('untappd_tools')) {
                wp_schedule_event(time(), 'untappd_tools', 'untappd_tools');
            }

            if (!wp_next_scheduled('untappd_tools_ratings')) {
                wp_schedule_event(time(), 'daily', 'untappd_tools_ratings');
            }

            add_action('untappd_tools', [$this, 'cron']);
            add_action('untappd_tools_ratings', [$this, 'cron_ratings']);
        } else {
            add_action('plugins_loaded', [$this, 'dev']);
        }
    }

    public function menu()
    {
        if (is_admin()) {
            add_management_page('Untappd Tools', 'Untappd Tools', 'manage_options', 'untappd-tools', ['UntappdTools\Admin', 'page']);
        }
    }

    public function cron()
    {
        $this->cron->checkins();
        $this->cron->toast();
        $this->cron->retail();
    }

    public function cron_ratings()
    {
        $this->cron->ratings();
        $this->cron->beers();
    }

    public function dev()
    {
        //$this->cron->retail();
    }
}