<?php

namespace App\Services;

use App\Models\City;
use App\Models\Mingle;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class MingleService
{
    /**
     * Create a Mingle.
     */
    public function create(
        User $user,
        array $data,
        ?UploadedFile $image = null
    ): Mingle {
        return DB::transaction(function () use ($user, $data, $image) {
            $city               =   City::query()->whereKey($data['city_id'])->where('is_active', true)->firstOrFail();
            $isEvent            =   $data['type'] === 'event';

            $mingle                     =   $user->mingles()->create([
                'type'                  =>  $data['type'],
                'title'                 =>  $data['title'],
                'description'           =>  $data['description'] ?? null,
                'city_id'               =>  $city->id,
                'category'              =>  $data['category'],
                'tags'                  =>  $this->normalizeTags($data['tags'] ?? null),
                'visibility'            =>  $data['visibility'],
                'maximum_attendees'     =>  $data['maximum_attendees'] ?? null,

                'venue'                 =>  $isEvent ? ($data['venue'] ?? null) : null,
                'address_details'       =>  $isEvent ? ($data['address_details'] ?? null): null,
                'meeting_instructions'  =>  $isEvent ? ($data['meeting_instructions'] ?? null): null,
                'starts_at'             =>  $isEvent ? $this->buildDateTime($data['date'] ?? null,
                        $data['start_time'] ?? null): null,
                'ends_at' => $isEvent
                    ? $this->buildDateTime(
                        $data['date'] ?? null,
                        $data['end_time'] ?? null
                    )
                    : null,

                'allow_guests'  => $isEvent && (bool) ($data['allow_guests'] ?? false),
                'is_recurring'  => $isEvent
                    && (bool) ($data['is_recurring'] ?? false),
                'is_featured'   => (bool) ($data['is_featured'] ?? false),
                'members_can_create_events' => ! $isEvent
                    && (bool) ($data['members_can_create_events'] ?? false),

                'image_path'    =>  $image?->store(
                    'mingles/' . $user->id,
                    'public'
                ),
            ]);

            /*
             * Creator automatically becomes an attendee/member.
             */

            $mingle->attendees()->syncWithoutDetaching([
                $user->id,
            ]);

            return $mingle;
        });
    }

    /**
     * Combine a date and time into a datetime string.
     */
    private function buildDateTime(
        ?string $date,
        ?string $time
    ): ?string {
        if (! $date || ! $time) {
            return null;
        }

        return $date . ' ' . $time;
    }

    /**
     * Normalize comma-separated tags.
     */
    private function normalizeTags(?string $tags): ?array
    {
        if (! $tags) {
            return null;
        }

        $tags = array_values(array_unique(array_filter(
            array_map('trim', explode(',', $tags)),
            static fn (string $tag) => $tag !== ''
        )));

        return $tags === [] ? null : $tags;
    }
}
