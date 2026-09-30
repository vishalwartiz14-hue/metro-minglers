@php($selectedInterestIds = old('interest_ids', $user->interestOptions->pluck('id')->all()))
<section>
    
    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-form">
        @csrf @method('patch')
        <div class="profile-upload-grid">
            <div><label for="profile_photo">Profile photo</label><input id="profile_photo" name="profile_photo"
                    type="file" class="dropify" accept="image/*" data-height="180" data-max-file-size="5M"
                    data-default-file="{{ $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : '' }}"
                    data-allowed-file-extensions="jpg jpeg png webp" />
                <x-input-error :messages="$errors->get('profile_photo')" />
                @if ($user->profile_photo_path)<label class="photo-remove"><input type="checkbox" name="remove_profile_photo" value="1"> Remove current profile photo</label>@endif
            </div>
            <div><label for="cover_photo">Cover photo</label><input id="cover_photo" name="cover_photo" type="file"
                    class="dropify" accept="image/*" data-height="180" data-max-file-size="5M"
                    data-default-file="{{ $user->cover_photo_path ? asset('storage/'.$user->cover_photo_path) : '' }}"
                    data-allowed-file-extensions="jpg jpeg png webp" />
                <x-input-error :messages="$errors->get('cover_photo')" />
                @if ($user->cover_photo_path)<label class="photo-remove"><input type="checkbox" name="remove_cover_photo" value="1"> Remove current cover photo</label>@endif
            </div>
        </div>

        <div class="profile-form-grid">
            <div class="profile-field"><label for="name">Name <span style="color:red;">*</span></label><input id="name" name="name" type="text"
                    value="{{ old('name', $user->name) }}" required autocomplete="name">
                <x-input-error :messages="$errors->get('name')" />
            </div>
            <div class="profile-field"><label for="email">Email address <span style="color:red;">*</span></label><input id="email" name="email"
                    type="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                <x-input-error :messages="$errors->get('email')" />
            </div>
            <div class="profile-field"><label for="date_of_birth">Date of birth <span style="color:red;">*</span></label><input id="date_of_birth"
                    name="date_of_birth" type="text"
                    value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}" readonly
                    required>
                <x-input-error :messages="$errors->get('date_of_birth')" />
            </div>
            <div class="profile-field"><label for="gender">Gender <span style="color:red;">*</span></label><select id="gender" name="gender" required>
                    <option value="">Select gender</option>
                    <option value="woman" @selected(old('gender',$user->gender) === 'woman')>Woman</option>
                    <option value="man" @selected(old('gender',$user->gender) === 'man')>Man</option>
                    <option value="non_binary" @selected(old('gender',$user->gender) === 'non_binary')>Non-binary
                    </option>
                    <option value="prefer_not_to_say" @selected(old('gender',$user->gender) ===
                        'prefer_not_to_say')>Prefer not to say</option>
                </select>
                <x-input-error :messages="$errors->get('gender')" />
            </div>
            <div class="profile-field"><label for="city_id">City <span style="color:red;">*</span></label><select id="city_id" name="city_id" required><option value="">Select your city</option>@foreach($cities as $city)<option value="{{ $city->id }}" @selected(old('city_id', $user->city_id) == $city->id)>{{ $city->name }}</option>@endforeach</select>
                <x-input-error :messages="$errors->get('city_id')" />
            </div>
            <div class="profile-field"><label for="zip_code">ZIP code <span style="color:red;">*</span></label><input id="zip_code" name="zip_code"
                    type="text" value="{{ old('zip_code',$user->zip_code) }}" required>
                <x-input-error :messages="$errors->get('zip_code')" />
            </div>
        </div>
        <div class="profile-field"><label for="about_me">About me</label><textarea id="about_me" name="about_me"
                rows="4"
                placeholder="Share a little about yourself, your vibe, and what you enjoy.">{{ old('about_me',$user->about_me) }}</textarea>
            <x-input-error :messages="$errors->get('about_me')" />
        </div>
        <div class="profile-form-grid">
            <div class="profile-field"><label for="occupation">Occupation</label><input id="occupation"
                    name="occupation" type="text" value="{{ old('occupation',$user->occupation) }}"></div>
            <div class="profile-field"><label for="education">Education</label><input id="education" name="education"
                    type="text" value="{{ old('education',$user->education) }}"></div>
        </div>
        <fieldset class="interest-list">
            <legend>What are you into? <small>Select up to 14 interests</small></legend>
            <div>@foreach($interests as $interest)<label><input type="checkbox" name="interest_ids[]"
                        value="{{ $interest->id }}" @checked(in_array($interest->id, $selectedInterestIds))><span>{{ $interest->name }}</span></label>@endforeach</div>
            <x-input-error :messages="$errors->get('interest_ids')" />
        </fieldset>
        <fieldset class="interest-list profile-goals">
            <legend>What brings you here? <small>Choose the experiences you want to find</small></legend>
            <input type="hidden" name="joining_reasons_present" value="1">
            <div>@foreach(['meet_people' => 'Meet new people', 'find_activities' => 'Find activities and events', 'go_out_more' => 'Go out more in my city', 'travel' => 'Find travel companions', 'network' => 'Build professional connections', 'dating' => 'Explore dating'] as $reason => $label)<label><input type="checkbox" name="joining_reasons[]" value="{{ $reason }}" @checked(in_array($reason, old('joining_reasons', $user->joining_reasons ?? [])))><span>{{ $label }}</span></label>@endforeach</div>
            <x-input-error :messages="$errors->get('joining_reasons')" />
        </fieldset>
        <label class="profile-dating-toggle"><input type="hidden" name="dating_mode" value="0"><input type="checkbox" name="dating_mode" value="1" @checked(old('dating_mode', $user->dating_mode))><span><b>Enable Dating Mode</b><small>Show me in optional dating discovery, separate from my main Mingle experience.</small></span></label>
        <footer class="profile-save"><span>@if(session('status') === 'profile-updated')✓ Profile saved
                successfully.@endif</span><button type="submit">Save profile <b>→</b></button></footer>
    </form>
</section>
