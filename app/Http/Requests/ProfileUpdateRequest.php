<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'date_of_birth'     =>  ['nullable', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'gender'            =>  ['nullable', 'in:woman,man,non_binary,prefer_not_to_say'],
            'city_id'           =>  ['nullable', 'exists:cities,id'],
            'zip_code'          =>  ['nullable', 'string', 'max:20'],
            'about_me'          =>  ['nullable', 'string', 'max:2000'],
            'occupation'        =>  ['nullable', 'string', 'max:150'],
            'education'         =>  ['nullable', 'string', 'max:150'],
            'interest_ids'      =>  ['nullable', 'array', 'max:14'],
            'interest_ids.*'    =>  ['integer', 'exists:interests,id'],
            'joining_reasons_present' => ['nullable', 'boolean'],
            'joining_reasons' => ['nullable', 'array', 'max:6'],
            'joining_reasons.*' => ['required', 'string', Rule::in(['meet_people', 'find_activities', 'go_out_more', 'travel', 'network', 'dating'])],
            'dating_mode' => ['nullable', 'boolean'],
            'profile_photo'     =>  ['nullable', 'image', 'max:5120'],
            'cover_photo'       =>  ['nullable', 'image', 'max:5120'],
            'remove_profile_photo' => ['nullable', 'boolean'],
            'remove_cover_photo' => ['nullable', 'boolean'],
        ];
    }
}
