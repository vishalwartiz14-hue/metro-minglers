<x-app-layout>

    <style>
        .cover-upload {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            height: auto;
            min-height: 220px;
            width: 100%;
            overflow: hidden;
            border: 1.5px dashed #d1d5db;
            border-radius: 16px;
            background: #fafafa;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease;
        }

        .cover-upload:hover {
            border-color: #9ca3af;
            background: #f7f7f7;
        }

        .cover-upload__input {
            position: absolute;
            inset: 0;
            z-index: 5;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .cover-upload__placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 35px 20px;
            text-align: center;
            pointer-events: none;
        }

        .cover-upload__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            margin-bottom: 14px;
            border-radius: 14px;
            background: #fff;
            color: #6b7280;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        .cover-upload__placeholder strong {
            display: block;
            margin-bottom: 5px;
            color: #1f2937;
            font-size: 15px;
            font-weight: 600;
        }

        .cover-upload__placeholder span {
            color: #6b7280;
            font-size: 13px;
        }

        .cover-upload__placeholder span u {
            color: #111827;
            font-weight: 600;
        }

        .cover-upload__placeholder small {
            display: block;
            margin-top: 10px;
            color: #9ca3af;
            font-size: 11px;
        }

        .cover-upload__preview {
            position: absolute;
            inset: 0;
            z-index: 2;
            width: 100%;
            height: 100%;
        }

        .cover-upload__preview img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            background: #eef0f3;
        }

        .review-cover {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 6;
            margin: 10px 0 16px;
            border-radius: 8px;
            object-fit: contain;
            object-position: center;
            background: #eef0f3;
        }

        .cover-upload__overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.45);
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none;
        }

        .cover-upload__preview:hover .cover-upload__overlay {
            opacity: 1;
        }

        .cover-upload__overlay span {
            padding: 9px 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.95);
            color: #111827;
            font-size: 13px;
            font-weight: 600;
        }

        [x-cloak] {
            display: none !important;
        }

        .field-error {
            display: block;
            margin-top: 4px;
            color: #e11d48;
            font-size: 11px;
            line-height: 1.4;
            font-weight: 500;
        }

        @media (max-width: 640px) {
            .cover-upload {
                min-height: 180px;
                border-radius: 12px;
            }

            .cover-upload__placeholder {
                padding: 25px 15px;
            }

            .cover-upload__icon {
                width: 54px;
                height: 54px;
            }
        }
    </style>

    <div
        class="mingle-page"
        x-data="mingleWizard()"
        x-init="init()"
    >

        <div class="mingle-shell">

            <a href="{{ route('dashboard') }}" class="mingle-back">
                ‹ <span>Back to home</span>
            </a>

            {{-- ==========================================================
                 STEP 0 — SELECT MINGLE TYPE
            =========================================================== --}}

            <div x-show="step === 0" x-cloak>

                <header class="mingle-title">

                    <p>CREATE A MINGLE</p>

                    <h1>What are you bringing to life ?</h1>

                    <span>Pick a format. You can make it yours from there.</span>

                </header>

                <div class="mingle-type-grid">

                    <a
                        href="{{ route('mingles.events.create') }}"
                        class="mingle-type-card mingle-type-card--event"
                    >
                        <span class="type-icon">▣</span>

                        <div>
                            <b>Event Mingle</b>

                            <p>
                                A specific date and time. Brunch, workout,
                                concert, boat day — whatever brings people together.
                            </p>
                        </div>

                        <i>›</i>
                    </a>

                    <a
                        href="{{ route('mingles.communities.create') }}"
                        class="mingle-type-card mingle-type-card--community"
                    >
                        <span class="type-icon">♣</span>

                        <div>
                            <b>Community Mingle</b>

                            <p>
                                An ongoing space based on a shared interest.
                                Members can connect and create together.
                            </p>
                        </div>

                        <i>›</i>
                    </a>

                </div>

                <p class="mingle-tip">
                    One community. Endless possibilities.
                    You can create events inside a community later.
                </p>

            </div>


            {{-- ==========================================================
                 MAIN FORM
            =========================================================== --}}

            <form
                x-ref="form"
                x-show="step > 0"
                x-cloak
                method="POST"
                action="{{ route('mingles.store') }}"
                enctype="multipart/form-data"
                class="mingle-form"
                @submit="submitting = true; syncType()"
                novalidate
            >

                @csrf

                <input type="hidden" name="type" :value="type">

                {{-- ======================================================
                     HEADER
                ======================================================= --}}

                <header class="wizard-header">

                    <p>CREATE A MINGLE</p>

                    <h1 x-text="type === 'event' ? 'Create an Event' : 'Create a Community'"></h1>

                </header>


                {{-- ======================================================
                     SERVER ERRORS
                ======================================================= --}}

                @if ($errors->any())

                    <div class="mingle-alert mingle-alert--error" role="alert" aria-live="assertive">

                        <strong>Please check the highlighted fields.</strong>

                        <ul>

                            @foreach ($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif


                {{-- ======================================================
                     PROGRESS
                ======================================================= --}}

                <div class="wizard-progress">

                    <template x-for="(label, index) in steps" :key="label">

                        <div
                            :class="{
                                'is-active': step === index + 1,
                                'is-complete': step > index + 1
                            }"
                        >
                            <span x-text="step > index + 1 ? '✓' : index + 1"></span>

                            <small x-text="label"></small>
                        </div>

                    </template>

                </div>


                {{-- ======================================================
                     STEP 1 — DETAILS
                ======================================================= --}}

                <section x-show="step === 1" class="wizard-panel" x-cloak>

                    <h2 x-text="type === 'event' ? 'Event Details' : 'Community Details'"></h2>


                    {{-- COVER IMAGE --}}

                    <label class="cover-upload">

                        <input
                            type="file"
                            name="image"
                            accept="image/jpeg,image/png,image/webp"
                            class="cover-upload__input"
                            @change="previewImage($event)"
                        >

                        {{-- DEFAULT STATE --}}

                        <div class="cover-upload__placeholder" x-show="!imagePreview">

                            <div class="cover-upload__icon">

                                <svg width="42" height="42" viewBox="0 0 24 24" fill="none">

                                    <path
                                        d="M12 16V4M12 4L7.5 8.5M12 4L16.5 8.5"
                                        stroke="currentColor"
                                        stroke-width="1.6"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />

                                    <path
                                        d="M5 14.5V18C5 19.1 5.9 20 7 20H17C18.1 20 19 19.1 19 18V14.5"
                                        stroke="currentColor"
                                        stroke-width="1.6"
                                        stroke-linecap="round"
                                    />

                                </svg>

                            </div>

                            <strong>Drop your cover photo here</strong>

                            <span>or <u>browse</u> to choose a file</span>

                            <small>JPG, PNG or WEBP · Maximum 5 MB</small>

                        </div>


                        {{-- PREVIEW STATE --}}

                        <div class="cover-upload__preview" x-show="imagePreview" x-cloak>

                            <img :src="imagePreview" alt="Cover preview">

                            <div class="cover-upload__overlay">
                                <span>Change cover photo</span>
                            </div>

                        </div>

                    </label>


                    <span
                        class="field-error"
                        x-show="fieldErrors.image"
                        x-text="fieldErrors.image"
                        x-cloak
                    ></span>

                    @error('image')

                        <span class="field-error">{{ $message }}</span>

                    @enderror


                    {{-- TITLE --}}

                    <div class="form-field">

                        <label for="title">

                            <span x-text="type === 'event' ? 'Event title' : 'Community name'"></span>

                            <em>*</em>

                        </label>

                        <input
                            id="title"
                            name="title"
                            type="text"
                            value="{{ old('title') }}"
                            placeholder="Enter title"
                            maxlength="150"
                            required
                            class="{{ $errors->has('title') ? 'is-invalid' : '' }}"
                        >

                        <span
                            class="field-error"
                            x-show="fieldErrors.title"
                            x-text="fieldErrors.title"
                            x-cloak
                        ></span>

                        @error('title')

                            <span class="field-error">{{ $message }}</span>

                        @enderror

                    </div>


                    {{-- DESCRIPTION --}}

                    <div class="form-field">

                        <label for="description">Description</label>

                        <textarea
                            id="description"
                            name="description"
                            maxlength="2000"
                            placeholder="Enter description (optional)"
                            class="{{ $errors->has('description') ? 'is-invalid' : '' }}"
                        >{{ old('description') }}</textarea>

                        @error('description')

                            <span class="field-error">{{ $message }}</span>

                        @enderror

                    </div>


                    {{-- CATEGORY --}}

                    <div class="form-field">

                        <label for="category">

                            Category

                            <em>*</em>

                        </label>

                        <select
                            id="category"
                            name="category"
                            required
                            class="{{ $errors->has('category') ? 'is-invalid' : '' }}"
                        >

                            <option value="">Select a category</option>

                            @foreach ($categories as $category)

                                <option
                                    value="{{ $category->name }}"
                                    @selected(old('category') === $category->name)
                                >
                                    {{ $category->name }}
                                </option>

                            @endforeach

                        </select>

                        <span class="field-error"  x-show="fieldErrors.category"x-text="fieldErrors.category"
                            x-cloak></span>

                        @error('category')
                            <span class="field-error">{{ $message }}</span>
                        @enderror

                    </div>


                    {{-- COMMUNITY TAGS --}}

                    <div class="form-field" x-show="type === 'community'" x-cloak>

                        <label for="tags">Tags</label>

                        <input
                            id="tags"
                            name="tags"
                            type="text"
                            value="{{ old('tags') }}"
                            maxlength="300"
                            :disabled="type !== 'community'"
                            placeholder="e.g. hiking, food, travel, local"
                            class="{{ $errors->has('tags') ? 'is-invalid' : '' }}"
                        >

                        <small class="field-hint">Separate multiple tags with commas.</small>

                        @error('tags')

                            <span class="field-error">{{ $message }}</span>

                        @enderror

                    </div>

                </section>


                {{-- ======================================================
                     STEP 2 — EVENT DATE / LOCATION
                ======================================================= --}}

                <section x-show="type === 'event' && step === 2" class="wizard-panel" x-cloak>

                    <h2>Date & Location</h2>

                    <p class="wizard-intro">When and where is your event?</p>


                    {{-- DATE / START TIME --}}

                    <div class="wizard-two">

                        <div class="form-field">

                            <label for="date">

                                Date

                                <em>*</em>

                            </label>

                            <input
                                id="date"
                                name="date"
                                type="date"
                                value="{{ old('date') }}"
                                min="{{ now()->toDateString() }}"
                                :disabled="type !== 'event'"
                                required
                                class="{{ $errors->has('date') ? 'is-invalid' : '' }}"
                            >

                            <span
                                class="field-error"
                                x-show="fieldErrors.date"
                                x-text="fieldErrors.date"
                                x-cloak
                            ></span>

                            @error('date')

                                <span class="field-error">{{ $message }}</span>

                            @enderror

                        </div>


                        <div class="form-field">

                            <label for="start_time">

                                Start time

                                <em>*</em>

                            </label>

                            <input
                                id="start_time"
                                name="start_time"
                                type="time"
                                value="{{ old('start_time') }}"
                                :disabled="type !== 'event'"
                                required
                                class="{{ $errors->has('start_time') ? 'is-invalid' : '' }}"
                            >

                            <span
                                class="field-error"
                                x-show="fieldErrors.start_time"
                                x-text="fieldErrors.start_time"
                                x-cloak
                            ></span>

                            @error('start_time')

                                <span class="field-error">{{ $message }}</span>

                            @enderror

                        </div>

                    </div>


                    {{-- END TIME --}}

                    <div class="form-field">

                        <label for="end_time">

                            End time

                            <small>(optional)</small>

                        </label>

                        <input
                            id="end_time"
                            name="end_time"
                            type="time"
                            value="{{ old('end_time') }}"
                            :disabled="type !== 'event'"
                            class="{{ $errors->has('end_time') ? 'is-invalid' : '' }}"
                        >

                        @error('end_time')

                            <span class="field-error">{{ $message }}</span>

                        @enderror

                    </div>


                    {{-- VENUE --}}

                    <div class="form-field">

                        <label for="venue">

                            Location

                            <em>*</em>

                        </label>

                        <input
                            id="venue"
                            name="venue"
                            type="text"
                            value="{{ old('venue') }}"
                            :disabled="type !== 'event'"
                            required
                            maxlength="200"
                            placeholder="Search for a venue or location"
                            class="{{ $errors->has('venue') ? 'is-invalid' : '' }}"
                        >

                        <span
                            class="field-error"
                            x-show="fieldErrors.venue"
                            x-text="fieldErrors.venue"
                            x-cloak
                        ></span>

                        @error('venue')

                            <span class="field-error">{{ $message }}</span>

                        @enderror

                    </div>


                    {{-- LOCATION PREVIEW --}}

                    <div class="location-preview">

                        <span>●</span>

                        <b>Your event location</b>

                        <small>Share a place people can easily find.</small>

                    </div>


                    {{-- ADDRESS DETAILS --}}

                    <div class="form-field">

                        <label for="address_details">

                            Address details

                            <small>(optional)</small>

                        </label>

                        <input
                            id="address_details"
                            name="address_details"
                            type="text"
                            value="{{ old('address_details') }}"
                            :disabled="type !== 'event'"
                            placeholder="Suite, parking notes, or venue details"
                            class="{{ $errors->has('address_details') ? 'is-invalid' : '' }}"
                        >

                        @error('address_details')

                            <span class="field-error">{{ $message }}</span>

                        @enderror

                    </div>


                    {{-- MEETING INSTRUCTIONS --}}

                    <div class="form-field">

                        <label for="meeting_instructions">Meeting instructions</label>

                        <textarea
                            id="meeting_instructions"
                            name="meeting_instructions"
                            :disabled="type !== 'event'"
                            class="{{ $errors->has('meeting_instructions') ? 'is-invalid' : '' }}"
                        >{{ old('meeting_instructions') }}</textarea>

                        @error('meeting_instructions')

                            <span class="field-error">{{ $message }}</span>

                        @enderror

                    </div>

                </section>


                {{-- ======================================================
                     SETTINGS
                ======================================================= --}}

                <section
                    x-show="
                        (type === 'event' && step === 3) ||
                        (type === 'community' && step === 2)
                    "
                    class="wizard-panel"
                    x-cloak
                >

                    <h2 x-text="type === 'event' ? 'Event Settings' : 'Community Settings'"></h2>

                    <p class="wizard-intro">Choose who can join and how it works.</p>


                    {{-- CITY --}}

                    <div class="form-field">

                        <label for="city_id">

                            City / Location

                            <em>*</em>

                        </label>

                        <select
                            id="city_id"
                            name="city_id"
                            required
                            class="{{ $errors->has('city_id') ? 'is-invalid' : '' }}"
                        >

                            <option value="">Select your city</option>

                            @foreach ($cities as $city)

                                <option
                                    value="{{ $city->id }}"
                                    @selected(old('city_id', $user->city_id) == $city->id)
                                >
                                    {{ $city->name }}
                                </option>

                            @endforeach

                        </select>

                        <span
                            class="field-error"
                            x-show="fieldErrors.city_id"
                            x-text="fieldErrors.city_id"
                            x-cloak
                        ></span>

                        @error('city_id')

                            <span class="field-error">{{ $message }}</span>

                        @enderror

                    </div>


                    {{-- VISIBILITY --}}

                    <fieldset class="choice-field">

                        <legend>

                            <span x-text="type === 'event' ? 'Who can join?' : 'Group visibility'"></span>

                            <em>*</em>

                        </legend>


                        {{-- EVENT VISIBILITY --}}

                        <template x-if="type === 'event'">

                            <div>

                                <label>

                                    <input
                                        type="radio"
                                        name="visibility"
                                        value="open"
                                        @checked(old('visibility', 'open') === 'open')
                                    >

                                    <span>
                                        <b>Open to all</b>
                                        <small>Anyone can find and join</small>
                                    </span>

                                </label>

                                <label>

                                    <input
                                        type="radio"
                                        name="visibility"
                                        value="connections"
                                        @checked(old('visibility') === 'connections')
                                    >

                                    <span>
                                        <b>Connections only</b>
                                        <small>Only your connections can join</small>
                                    </span>

                                </label>

                                <label>

                                    <input
                                        type="radio"
                                        name="visibility"
                                        value="invite"
                                        @checked(old('visibility') === 'invite')
                                    >

                                    <span>
                                        <b>Invite only</b>
                                        <small>Only people you invite can join</small>
                                    </span>

                                </label>

                            </div>

                        </template>


                        {{-- COMMUNITY VISIBILITY --}}

                        <template x-if="type === 'community'">

                            <div>

                                <label>

                                    <input
                                        type="radio"
                                        name="visibility"
                                        value="public"
                                        @checked(old('visibility', 'public') === 'public')
                                    >

                                    <span>
                                        <b>Public</b>
                                        <small>Anyone can find and join</small>
                                    </span>

                                </label>

                                <label>

                                    <input
                                        type="radio"
                                        name="visibility"
                                        value="private"
                                        @checked(old('visibility') === 'private')
                                    >

                                    <span>
                                        <b>Private</b>
                                        <small>Only approved people can join</small>
                                    </span>

                                </label>

                                <label>

                                    <input
                                        type="radio"
                                        name="visibility"
                                        value="request"
                                        @checked(old('visibility') === 'request')
                                    >

                                    <span>
                                        <b>Request to join</b>
                                        <small>People can request to join</small>
                                    </span>

                                </label>

                            </div>

                        </template>

                    </fieldset>


                    {{-- VISIBILITY ERROR --}}

                    <span
                        class="field-error"
                        x-show="fieldErrors.visibility"
                        x-text="fieldErrors.visibility"
                        x-cloak
                    ></span>

                    @error('visibility')

                        <span class="field-error">{{ $message }}</span>

                    @enderror


                    {{-- MAXIMUM ATTENDEES --}}

                    <div class="form-field">

                        <label for="maximum_attendees">

                            <span x-text="type === 'event' ? 'Maximum attendees (optional)' : 'Maximum members (optional)'"></span>

                        </label>

                        <input
                            id="maximum_attendees"
                            name="maximum_attendees"
                            type="number"
                            min="1"
                            max="100000"
                            value="{{ old('maximum_attendees') }}"
                            placeholder="No limit"
                            class="{{ $errors->has('maximum_attendees') ? 'is-invalid' : '' }}"
                        >

                        @error('maximum_attendees')

                            <span class="field-error">{{ $message }}</span>

                        @enderror

                    </div>


                    {{-- ALLOW GUESTS --}}

                    <label class="switch-field" x-show="type === 'event'" x-cloak>

                        <span>
                            <b>Allow guests</b>
                            <small>Members can bring a guest</small>
                        </span>

                        <input
                            type="checkbox"
                            name="allow_guests"
                            value="1"
                            @checked(old('allow_guests', true))
                        >

                        <i></i>

                    </label>


                    {{-- RECURRING --}}

                    <label class="switch-field" x-show="type === 'event'" x-cloak>

                        <span>
                            <b>Make this recurring</b>
                            <small>Repeat this event</small>
                        </span>

                        <input
                            type="checkbox"
                            name="is_recurring"
                            value="1"
                            @checked(old('is_recurring'))
                        >

                        <i></i>

                    </label>


                    {{-- MEMBER EVENTS --}}

                    <label class="switch-field" x-show="type === 'community'" x-cloak>

                        <span>
                            <b>Allow members to create events</b>
                            <small>Let members bring the community together</small>
                        </span>

                        <input
                            type="checkbox"
                            name="members_can_create_events"
                            value="1"
                            @checked(old('members_can_create_events', true))
                        >

                        <i></i>

                    </label>


                    {{-- FEATURED --}}

                    <label class="switch-field">

                        <span>
                            <b>
                                Feature this Mingle
                                <small>(Premium)</small>
                            </b>

                            <small>Get more visibility in your city</small>
                        </span>

                        <input
                            type="checkbox"
                            name="is_featured"
                            value="1"
                            @checked(old('is_featured'))
                        >

                        <i></i>

                    </label>

                </section>


                {{-- ======================================================
                     REVIEW
                ======================================================= --}}

                <section x-show="step === steps.length" class="wizard-panel wizard-review" x-cloak>

                    <h2 x-text="type === 'event' ? 'Review & Post' : 'Review & Create'"></h2>

                    <p class="wizard-intro">Review your Mingle before publishing.</p>


                    <div class="review-card">

                        <span
                            class="review-badge"
                            x-text="type === 'event' ? 'EVENT MINGLE' : 'COMMUNITY MINGLE'"
                        ></span>


                        <template x-if="imagePreview">

                            <img
                                :src="imagePreview"
                                class="review-cover"
                                alt="Mingle cover"
                            >

                        </template>


                        <h3 x-text="formValue('title', 'Your Mingle name')"></h3>

                        <p x-text="formValue('description', 'Your description will appear here.')"></p>


                        <dl>

                            <div>

                                <dt>Category</dt>

                                <dd x-text="formValue('category', 'Not selected')"></dd>

                            </div>


                            <div>

                                <dt>City</dt>

                                <dd x-text="selectedCity()"></dd>

                            </div>


                            <template x-if="type === 'event'">

                                <div>

                                    <dt>When</dt>

                                    <dd>

                                        <span x-text="formValue('date', 'Select date')"></span>

                                        <span>·</span>

                                        <span x-text="formValue('start_time', 'Select time')"></span>

                                    </dd>

                                </div>

                            </template>


                            <template x-if="type === 'event'">

                                <div>

                                    <dt>Location</dt>

                                    <dd x-text="formValue('venue', 'Not selected')"></dd>

                                </div>

                            </template>


                            <div>

                                <dt>Visibility</dt>

                                <dd x-text="visibilityLabel()"></dd>

                            </div>


                            <div>

                                <dt>Capacity</dt>

                                <dd x-text="formValue('maximum_attendees', 'No limit')"></dd>

                            </div>

                        </dl>

                    </div>

                </section>


                {{-- ======================================================
                     ACTIONS
                ======================================================= --}}

                <div class="wizard-actions">

                    <button
                        type="button"
                        class="wizard-secondary"
                        @click="back()"
                        :disabled="submitting"
                    >
                        Back
                    </button>


                    <button
                        type="button"
                        class="wizard-primary"
                        x-show="step < steps.length"
                        @click="next()"
                        :disabled="submitting"
                    >
                        Next <span>›</span>
                    </button>


                    <button
                        type="submit"
                        class="wizard-primary"
                        x-show="step === steps.length"
                        :disabled="submitting"
                    >

                        <span x-show="!submitting">➤</span>

                        <span
                            x-text="
                                submitting
                                    ? 'Creating...'
                                    : (type === 'event' ? 'Post Mingle' : 'Create Community')
                            "
                        ></span>

                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- ================================================================
         ALPINE WIZARD
    ================================================================= --}}

    <script>
        function mingleWizard() {
            return {

                type: @json(old('type', $selectedType ?? '')),

                step: @json(old('type', $selectedType ?? '') ? 1 : 0),

                isTypeSpecificRoute: @json($selectedType !== null),

                typeSelectorUrl: @json(route('mingles.create')),

                submitting: false,

                imagePreview: null,

                fieldErrors: {},

                // A stable snapshot used by the review step. Reading values
                // from hidden panels can be unreliable after Alpine restores
                // a local draft, so the review never depends on hidden DOM.
                review: {},

                get steps() {
                    return this.type === 'community'
                        ? ['Details', 'Settings', 'Review']
                        : ['Details', 'Date & Location', 'Settings', 'Review'];
                },


                /*
                |----------------------------------------------------------------------
                | INITIALIZE
                |----------------------------------------------------------------------
                | Server-rendered old input restores a failed submission.
                | A fresh visit starts at the type selector so the user can
                | deliberately choose Event or Community.
                |----------------------------------------------------------------------
                */

                init() {
                    const serverInput = @json(old());
                    const errorFields = @json($errors->keys());

                    if (Object.keys(serverInput).length > 0) {
                        if (this.type) {
                            this.step = errorFields.length
                                ? this.stepForField(errorFields[0])
                                : 1;
                        }

                        return;
                    }
                },


                /*
                |----------------------------------------------------------------------
                | DRAFT PERSISTENCE
                |----------------------------------------------------------------------
                | Saves type, step and field values to localStorage so a
                | page refresh resumes exactly where the user left off.
                |----------------------------------------------------------------------
                */

                saveDraft() {
                    const form = this.$refs.form;

                    if (!form || !this.type) {
                        return;
                    }

                    const data = {};

                    new FormData(form).forEach((value, key) => {
                        if (key !== '_token' && typeof value === 'string') {
                            data[key] = value;
                        }
                    });

                    // Alpine's :value binding flushes on a microtask, so the
                    // hidden input may still be stale here — state is truth.
                    data.type = this.type;

                    try {
                        localStorage.setItem(
                            this.draftKey,
                            JSON.stringify({
                                type: this.type,
                                step: this.step,
                                data,
                                savedAt: Date.now()
                            })
                        );
                    } catch (error) {
                        // Storage unavailable — wizard simply won't resume.
                    }
                },


                clearDraft() {
                    try {
                        localStorage.removeItem(this.draftKey);
                    } catch (error) {
                        // Ignore storage errors.
                    }
                },


                /*
                |----------------------------------------------------------------------
                | SUBMIT-TIME TYPE SYNC
                |----------------------------------------------------------------------
                | Guarantees the hidden type input matches state right before
                | the POST, regardless of Alpine's async binding flush.
                |----------------------------------------------------------------------
                */

                syncType() {
                    const input = this.$refs.form?.querySelector(
                        'input[name="type"]'
                    );

                    if (input) {
                        input.value = this.type;
                    }
                },


                restoreFields(data) {
                    const form = this.$refs.form;

                    if (!form || !data) {
                        return;
                    }

                    Object.entries(data).forEach(([name, value]) => {
                        // _token: must stay fresh per page.
                        // type: driven by the :value binding from state —
                        // restoring a stale draft value would clobber it.
                        if (name === '_token' || name === 'type') {
                            return;
                        }

                        const elements =
                            form.querySelectorAll(`[name="${name}"]`);

                        elements.forEach((el) => {
                            if (el.type === 'radio') {
                                el.checked = el.value === value;
                            } else if (el.type === 'checkbox') {
                                el.checked = true;
                            } else if (el.type !== 'file') {
                                el.value = value;
                            }
                        });
                    });

                    // Checkboxes absent from the draft were unchecked.
                    form
                        .querySelectorAll('input[type="checkbox"][name]')
                        .forEach((el) => {
                            if (!(el.name in data)) {
                                el.checked = false;
                            }
                        });
                },


                selectType(type) {
                    if (this.type && this.type !== type) {
                        this.resetForm();
                    }

                    this.type = type;
                    this.fieldErrors = {};
                    this.review = {};
                    this.step = 1;
                },


                back() {
                    this.fieldErrors = {};

                    if (this.step === 1 && this.isTypeSpecificRoute) {
                        window.location.href = this.typeSelectorUrl;
                        return;
                    }

                    this.step = this.step > 1 ? this.step - 1 : 0;
                },


                next() {
                    if (this.validateCurrentStep() && this.step < this.steps.length) {
                        this.syncReview();
                        this.step++;
                    }
                },


                resetForm() {
                    this.$refs.form?.reset();
                    this.imagePreview = null;
                },


                /*
                |----------------------------------------------------------------------
                | REVIEW SNAPSHOT
                |----------------------------------------------------------------------
                | The review panel has a single data source. This keeps the
                | summary correct even when earlier wizard panels are hidden.
                |----------------------------------------------------------------------
                */

                syncReview() {
                    const form = this.$refs.form;

                    if (!form) {
                        return;
                    }

                    const values = {};

                    new FormData(form).forEach((value, name) => {
                        if (typeof value === 'string') {
                            values[name] = value;
                        }
                    });

                    const city = form.querySelector('[name="city_id"]');
                    const visibility = form.querySelector(
                        'input[name="visibility"]:checked'
                    );

                    this.review = {
                        ...values,
                        city_label: city?.value
                            ? city.options[city.selectedIndex]?.text
                            : '',
                        visibility_label: visibility
                            ? this.visibilityLabels()[visibility.value]
                            : '',
                    };
                },


                stepForField(field) {
                    const eventDetails = [
                        'date',
                        'start_time',
                        'end_time',
                        'venue',
                        'address_details',
                        'meeting_instructions'
                    ];

                    const settings = [
                        'city_id',
                        'visibility',
                        'maximum_attendees',
                        'allow_guests',
                        'is_recurring',
                        'is_featured',
                        'members_can_create_events'
                    ];

                    if (eventDetails.includes(field)) {
                        return 2;
                    }

                    if (settings.includes(field)) {
                        return this.type === 'community' ? 2 : 3;
                    }

                    return 1;
                },


                requiredFields() {
                    const byStep = {
                        1: ['title', 'category'],
                        2: this.type === 'event'
                            ? ['date', 'start_time', 'venue']
                            : ['city_id', 'visibility'],
                        3: this.type === 'event'
                            ? ['city_id', 'visibility']
                            : []
                    };

                    return byStep[this.step] || [];
                },


                requiredMessage(field) {
                    const messages = {
                        title: this.type === 'event'
                            ? 'Please enter an event title.'
                            : 'Please enter a community name.',
                        category: 'Please select a category.',
                        date: 'Please select an event date.',
                        start_time: 'Please select a start time.',
                        venue: 'Please enter the event location.',
                        city_id: 'Please select a city.',
                        visibility: 'Please select who can join.'
                    };

                    return messages[field] || 'This field is required.';
                },


                validateCurrentStep() {
                    const form = this.$refs.form;

                    if (!form) {
                        return true;
                    }

                    this.fieldErrors = {};

                    const fields = this.requiredFields();
                    let valid = true;

                    fields.forEach((name) => {
                        const field = form.querySelector(`[name="${name}"]`);

                        if (!field || field.disabled) {
                            return;
                        }

                        if (name === 'visibility') {
                            if (!form.querySelector('input[name="visibility"]:checked')) {
                                this.fieldErrors.visibility = this.requiredMessage(name);
                                valid = false;
                            }

                            return;
                        }

                        if (!field.value.trim()) {
                            this.fieldErrors[name] = this.requiredMessage(name);
                            field.classList.add('is-invalid');
                            valid = false;

                            return;
                        }

                        field.classList.remove('is-invalid');
                    });

                    if (!valid) {
                        const firstInvalid = fields.find((name) => this.fieldErrors[name]);
                        form.querySelector(`[name="${firstInvalid}"]`)?.focus();
                    }

                    return valid;
                },


                previewImage(event) {
                    const file = event.target.files[0];

                    if (!file) {
                        this.imagePreview = null;
                        return;
                    }

                    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

                    if (!allowedTypes.includes(file.type)) {
                        this.rejectImage(event, 'Please select a JPG, PNG, or WEBP image.');
                        return;
                    }

                    if (file.size > 5 * 1024 * 1024) {
                        this.rejectImage(event, 'The cover photo must be 5 MB or smaller.');
                        return;
                    }

                    delete this.fieldErrors.image;
                    this.imagePreview = URL.createObjectURL(file);
                },


                rejectImage(event, message) {
                    this.fieldErrors.image = message;
                    this.imagePreview = null;
                    event.target.value = '';
                },


                formValue(name, fallback = '') {
                    const field = this.$refs.form?.querySelector(`[name="${name}"]`);

                    return (this.review[name] ?? field?.value) || fallback;
                },


                selectedCity() {
                    if (this.review.city_label) {
                        return this.review.city_label;
                    }

                    const select = this.$refs.form?.querySelector('[name="city_id"]');

                    if (!select?.value) {
                        return 'Not selected';
                    }

                    return select.options[select.selectedIndex]?.text || 'Not selected';
                },


                visibilityLabel() {
                    if (this.review.visibility_label) {
                        return this.review.visibility_label;
                    }

                    const selected = this.$refs.form?.querySelector(
                        'input[name="visibility"]:checked'
                    );

                    if (!selected) {
                        return 'Not selected';
                    }

                    return this.visibilityLabels()[selected.value] || selected.value;
                },


                visibilityLabels() {
                    return {
                        open: 'Open to all',
                        connections: 'Connections only',
                        invite: 'Invite only',
                        public: 'Public',
                        private: 'Private',
                        request: 'Request to join'
                    };
                }

            };
        }
    </script>
</x-app-layout>
<!-- design landling page first -->
