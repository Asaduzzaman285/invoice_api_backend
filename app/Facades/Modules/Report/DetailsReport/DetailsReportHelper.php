<?php

namespace App\Facades\Modules\Report\DetailsReport;

use App\Models\Configuration\Service;


class DetailsReportHelper {
    public function processGetServiceId( $handset_user = null, $service = null, $sub_hotkey = null ) {
        $service_id = null;

        if ( isset( $service ) ) {
            // Jokes, Hadith

            $hotkey = $this->getHotkey( $handset_user, strtolower( $service ) );
            $service_id = Service::whereRaw( 'LOWER(service) = ?', [ strtolower( $service ) ] )
            ->whereRaw( 'LOWER(hotkey) = ?', [ strtolower( $hotkey ) ] )
            ->whereRaw( 'LOWER(sub_hotkey) = ?', [ strtolower( $sub_hotkey ) ] )
            ->pluck( 'id' )
            ->first();

            // dd($service_id);

            if ( !isset( $service_id ) ) {
                $service = Service::create( [
                    'service' => $service,
                    'hotkey' => $hotkey,
                    'sub_hotkey' => $sub_hotkey,
                ] );

                $service_id = $service[ 'id' ];
            }
        }

        return $service_id;
    }

    public function getHotkey( $handset_user,  $service) {
        // to check form db exact column name
        $services = collect([
            'k_cricket_update' => 'cricket update',
            'k_hadith' => 'hadith',
            'k_jokes' => 'jokes',
            'k_beauty_tips' => 'beauty tips',
            'k_media_gossip' => 'media gossip',
            'k_love_tips' => 'love tips'
        ]);
        $keyName = $services->filter(function ($value) use ($service) {
            return stripos($value, $service) !== false; // Case-insensitive search
        })->keys()->first();

        // dd($handset_user[$keyName]);

        $hotkey = explode(' ', $handset_user[$keyName] ?? '')[0] ?? null;
        // dd($hotkey);
        return $hotkey;
    }

}
