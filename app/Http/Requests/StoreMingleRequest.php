<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMingleRequest extends FormRequest
{
    private const EVENT = 'event';
    private const COMMUNITY = 'community';
    private const EVENT_VISIBILITIES = ['open', 'connections', 'invite'];
    private const COMMUNITY_VISIBILITIES = ['public', 'private', 'request'];
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array {
        $type                   = $this->input('type');

        $visibilityOptions      = $type === self::COMMUNITY
            ? self::COMMUNITY_VISIBILITIES
            : self::EVENT_VISIBILITIES;

        $rules = [
            'type' => ['required',
                Rule::in([
                    self::EVENT,
                    self::COMMUNITY,
                ]),
            ],

            'title'         => [ 'required','string','min:3','max:150',],
            'description'   => ['nullable','string','max:2000',],
            'city_id'       => ['required',
                Rule::exists('cities', 'id')->where('is_active', true),
            ],
            'category'      => ['required',
                Rule::exists('interests', 'name')->where('is_active', true),
            ],
            'visibility'    => ['required',
                Rule::in($visibilityOptions),
            ],
            'maximum_attendees' => ['nullable','integer','min:1','max:100000',],

            'image'         =>  ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120',],

            'allow_guests'  => ['nullable','boolean',],
            'is_recurring'  => ['nullable','boolean',],
            'is_featured'   => ['nullable','boolean',],
            'members_can_create_events'         =>  ['nullable','boolean',],
        ];

        if ($type === self::EVENT) {
            $rules += [
                'date'                  =>  ['required','date','after_or_equal:today',],
                'start_time'            =>  ['required','date_format:H:i',],
                'end_time'              =>  ['nullable','date_format:H:i','after:start_time',],
                'venue'                 =>  ['required','string','max:200',],
                'address_details'       =>  ['nullable','string','max:255',],
                'meeting_instructions'  =>  ['nullable','string','max:1000',],
            ];
        }

        if ($type === self::COMMUNITY) {
            $rules += [
                'tags'                  =>  ['nullable','string','max:300',],
            ];
        }
        return $rules;
    }

    /**
     * Validate the complete event start datetime. Field-level date and time
     * rules cannot determine whether an event scheduled for today is already
     * in the past.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('type') !== self::EVENT
                || $validator->errors()->hasAny(['date', 'start_time'])) {
                return;
            }

            $startsAt = CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                $this->input('date').' '.$this->input('start_time'),
                config('app.timezone')
            );

            if ($startsAt->isPast()) {
                $validator->errors()->add(
                    'start_time',
                    'The event start time must be in the future.'
                );
            }
        });
    }

    public function messages(): array{
        return [
            'type.required'         =>  'Please select a Mingle type.',
            'type.in'               =>  'Please select a valid Mingle type.',
            'title.required'        =>  'Please enter a name for your Mingle.',
            'title.min'             =>  'The title must be at least 3 characters.',
            'title.max'             =>  'The title may not be longer than 150 characters.',
            'description.max'       =>  'The description may not be longer than 2000 characters.',
            'city_id.required'      =>  'Please select a city.',
            'city_id.exists'        =>  'The selected city is not available.',
            'category.required'     =>  'Please select a category.',
            'category.exists'       =>  'The selected category is not available.',
            'visibility.required'   =>  'Please choose who can join.',
            'visibility.in'         =>  'Please choose a valid visibility option.',
            'date.required'         =>  'Please select an event date.',
            'date.after_or_equal'   =>  'The event date must be today or later.',
            'start_time.required'   =>  'Please select a start time.',
            'start_time.date_format' => 'Please enter a valid start time.',
            'end_time.after'        =>  'The end time must be after the start time.',
            'venue.required'        =>  'Please enter the event location.',
            'image.image'           =>  'The cover photo must be a valid image.',
            'image.mimes'           =>  'The cover photo must be JPG, JPEG, PNG, or WEBP.',
            'image.max'             =>  'The cover photo may not be larger than 5 MB.',
        ];
    }

    /**
     * Prepare checkbox values for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'allow_guests'          => $this->boolean('allow_guests'),
            'is_recurring'          => $this->boolean('is_recurring'),
            'is_featured'           => $this->boolean('is_featured'),
            'members_can_create_events' => $this->boolean('members_can_create_events'),
        ]);
    }
}
