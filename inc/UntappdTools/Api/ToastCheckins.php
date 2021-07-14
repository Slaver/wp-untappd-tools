<?php

declare(strict_types=1);

namespace UntappdTools\Api;

use UntappdTools;
use UntappdTools\Api;
use UntappdPHP;

class ToastCheckins extends Api
{
    public function run()
    {
        $checkins = $this->checkins->getCheckinsForToast();

        foreach ($checkins as $checkin) {
            $untappd = new UntappdPHP(
                $this->options['client_id'],
                $this->options['client_secret'], '',
                $this->options['access_token']
            );
            $data = $untappd->post("/checkin/toast/".$checkin->checkin_id);

            if (!empty($data->response->result) && $data->response->result == 'success') {
                $this->checkins->updateCheckin((int)$checkin->id, ['is_toasted' => true]);
                $this->log->write('Toast: '.$checkin->checkin_id);
            } else {
                if ($data->meta->error_detail == 'This check-in doesn\'t exist. It may have been deleted by the user.') {
                    $this->checkins->updateCheckin((int)$checkin->id, ['is_deleted' => true]);
                    $this->log->write('Deleted checkin: '.$checkin->checkin_id);
                } elseif ($data->meta->error_detail == 'You are not authorized to toast this checkin.') {
                    $this->checkins->updateCheckin((int)$checkin->id, ['is_toasted' => '-1']);
                    $this->log->error($checkin->checkin_id.' - '.$data->meta->error_detail);
                } else {
                    $this->log->error($checkin->checkin_id.' - '.$data->meta->error_detail);
                }
            }
        }
    }
}