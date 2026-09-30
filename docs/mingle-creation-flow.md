# Mingle creation flow

The create page supports two distinct records through one wizard:

Use `/mingles/create` to select a type. `/mingles/events/create` always opens the Event flow and `/mingles/communities/create` always opens the Community flow, including after a browser reload.

| Type | Steps | Required type-specific data |
| --- | --- | --- |
| Event | Details → Date & Location → Settings → Review | date, start time, venue |
| Community | Details → Settings → Review | optional comma-separated tags |

## Where to make a change

| Change needed | File to update |
| --- | --- |
| Add, remove, or reorder a visible wizard field | `resources/views/mingles/create.blade.php` |
| Change client-side step requirements or type-switch behaviour | `mingleWizard()` at the bottom of `resources/views/mingles/create.blade.php` |
| Change the list of active cities/categories | Admin Directory or the `cities` / `interests` tables; the controller reads only active records |
| Change server-side validation or error messages | `app/Http/Requests/StoreMingleRequest.php` |
| Map validated input to database columns, tags, uploads, or defaults | `app/Services/MingleService.php` |
| Add or rename persisted columns | a new migration, then `app/Models/Mingle.php` and `MingleService.php` |
| Change the post-create redirect or page data | `app/Http/Controllers/MingleController.php` |
| Add behaviour safely | `tests/Feature/MingleCreateTest.php` |

## Rules that must stay aligned

1. A field shown by the wizard must have matching validation in `StoreMingleRequest` and mapping in `MingleService`.
2. Event-only controls must be disabled for communities so they are never submitted accidentally.
3. If a field is required on a given step, add it to `requiredFields()` and `stepForField()` in the wizard as well as the server-side request rules.
4. A “recurring” event currently only stores the `is_recurring` flag. Do not promise recurring dates until a recurrence-pattern column and event-generation behaviour are implemented.

## Existing user-facing safeguards

- The browser checks required fields before moving forward.
- The server repeats every important validation, including active city/category checks.
- Switching from Event to Community (or the reverse) starts a clean form, so type-specific data never carries over accidentally.
- The creator is automatically added as the first attendee/member in the same database transaction that creates the Mingle.
