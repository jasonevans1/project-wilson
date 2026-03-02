# Tasks: Notification System

**Input**: Design documents from `/specs/006-notification-system/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Required — constitution mandates TDD (Red → Green → Refactor).

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create the foundational model, enum, migration, and factory shared by all user stories

- [x] T001 Create ReminderType enum with ThirtyDay, SevenDay, OneDay cases in `app/Enums/ReminderType.php`
- [x] T002 Scaffold MaintenanceReminder model with migration and factory via `php artisan make:model MaintenanceReminder -mf --no-interaction`
- [x] T003 Implement `maintenance_reminders` migration with all columns (user_id, maintenance_occurrence_id, reminder_type, sent_at, snoozed_until, snooze_count), foreign keys with cascade delete, unique constraint on (maintenance_occurrence_id, reminder_type), and indexes per `data-model.md` in `database/migrations/xxxx_create_maintenance_reminders_table.php`
- [x] T004 Implement MaintenanceReminder model with fillable fields, casts (reminder_type → ReminderType enum, sent_at → datetime, snoozed_until → date, snooze_count → integer), and relationships (user, occurrence) in `app/Models/MaintenanceReminder.php`
- [x] T005 Implement MaintenanceReminderFactory with default state and factory states for `sent`, `snoozed`, and `pending` in `database/factories/MaintenanceReminderFactory.php`
- [x] T006 Add `reminders()` HasMany relationship to MaintenanceOccurrence model in `app/Models/MaintenanceOccurrence.php`
- [x] T007 Add `maintenanceReminders()` HasMany relationship to User model in `app/Models/User.php`
- [x] T008 Run migration to verify schema via `php artisan migrate`

**Checkpoint**: MaintenanceReminder model ready with relationships, factory, and migration applied

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Create the Artisan command skeleton and route infrastructure needed by all user stories

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T009 Scaffold SendMaintenanceReminders command via `php artisan make:command SendMaintenanceReminders --no-interaction` in `app/Console/Commands/SendMaintenanceReminders.php`
- [x] T010 Set command signature to `maintenance:send-reminders` and add empty `handle()` method with return type in `app/Console/Commands/SendMaintenanceReminders.php`
- [x] T011 Register daily schedule for `maintenance:send-reminders` command in `routes/console.php`
- [x] T012 Create `routes/reminders.php` route file with placeholder structure and include it from `routes/web.php`

**Checkpoint**: Foundation ready — command and route skeleton in place, schedule registered

---

## Phase 3: User Story 1 — Receive 30-Day Advance Maintenance Reminder (Priority: P1) 🎯 MVP

**Goal**: Users receive a single email reminder 30 days before a maintenance occurrence is due

**Independent Test**: Create a maintenance task with a due date 30 days out, run `maintenance:send-reminders`, verify email sent with correct content (asset name, task description, due date, link)

### Tests for User Story 1

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T013 [P] [US1] Create test file via `php artisan make:test SendMaintenanceRemindersTest --pest --no-interaction` in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T014 [P] [US1] Create test file via `php artisan make:test MaintenanceReminderNotificationTest --pest --no-interaction` in `tests/Feature/MaintenanceReminderNotificationTest.php`
- [x] T015 [US1] Write test: it sends a 30-day reminder for an active occurrence due in exactly 30 days in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T016 [US1] Write test: it skips completed occurrences (completed_at not null) in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T017 [US1] Write test: it skips occurrences due in 31 days (not yet in window) in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T018 [US1] Write test: it skips occurrences for inactive maintenance tasks in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T019 [US1] Write test: it skips users without verified email in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T020 [US1] Write test: it does not send duplicate reminders for the same occurrence and interval in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T021 [US1] Write test: it catches up on missed notifications (due_date - 30 was yesterday) in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T022 [US1] Write test: notification email contains asset name, task name, due date, and view link in `tests/Feature/MaintenanceReminderNotificationTest.php`
- [x] T023 [US1] Write test: notification email subject is "Upcoming maintenance: {task} for {asset}" for 30-day type in `tests/Feature/MaintenanceReminderNotificationTest.php`

### Implementation for User Story 1

- [x] T024 [US1] Create MaintenanceReminderNotification class implementing ShouldQueue with mail channel, accepting MaintenanceReminder model, building MailMessage with subject, greeting, content (asset name, task name, description, due date), action button (view task URL), and urgency-based messaging in `app/Notifications/MaintenanceReminderNotification.php`
- [x] T025 [US1] Create single-reminder markdown email template with asset name, task description, due date, view task button, and placeholder snooze links in `resources/views/mail/maintenance-reminder.blade.php`
- [x] T026 [US1] Implement `handle()` method in SendMaintenanceReminders: query MaintenanceOccurrence where completed_at is null, parent task is_active is true, parent user email_verified_at is not null, due_date minus 30 days <= today; check for existing sent MaintenanceReminder records; create new records; dispatch MaintenanceReminderNotification for single reminders per user; update sent_at in `app/Console/Commands/SendMaintenanceReminders.php`
- [x] T027 [US1] Run US1 tests and verify all pass via `php artisan test --compact tests/Feature/SendMaintenanceRemindersTest.php tests/Feature/MaintenanceReminderNotificationTest.php`

**Checkpoint**: 30-day single-task reminder fully functional and tested independently

---

## Phase 4: User Story 2 — Receive Digest Email for Multiple Upcoming Tasks (Priority: P2)

**Goal**: When multiple reminders trigger for the same user on the same day, consolidate into a single digest email

**Independent Test**: Create 3 occurrences for the same user all due in 30 days, run command, verify one digest email sent listing all 3 tasks

### Tests for User Story 2

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T028 [P] [US2] Create test file via `php artisan make:test MaintenanceDigestNotificationTest --pest --no-interaction` in `tests/Feature/MaintenanceDigestNotificationTest.php`
- [x] T029 [US2] Write test: it sends a digest email when user has 3 reminders on the same day in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T030 [US2] Write test: it sends a single-task email (not digest) when user has only 1 reminder in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T031 [US2] Write test: digest notification contains all task names, asset names, and due dates in `tests/Feature/MaintenanceDigestNotificationTest.php`
- [x] T032 [US2] Write test: digest email subject is "Wilson: You have {count} upcoming maintenance tasks" in `tests/Feature/MaintenanceDigestNotificationTest.php`

### Implementation for User Story 2

- [x] T033 [US2] Create MaintenanceDigestNotification class implementing ShouldQueue with mail channel, accepting a collection of MaintenanceReminder models, building MailMessage with count, table of tasks (asset, task, due date, link), and subject line in `app/Notifications/MaintenanceDigestNotification.php`
- [x] T034 [US2] Create digest markdown email template with greeting, count summary, table listing each task (asset name, task name, due date, link), and footer in `resources/views/mail/maintenance-digest.blade.php`
- [x] T035 [US2] Update SendMaintenanceReminders handle() to group pending reminders by user_id and dispatch MaintenanceDigestNotification when count > 1 (instead of individual notifications) in `app/Console/Commands/SendMaintenanceReminders.php`
- [x] T036 [US2] Run US2 tests and verify all pass via `php artisan test --compact tests/Feature/MaintenanceDigestNotificationTest.php --filter=digest`

**Checkpoint**: Digest emails working — users with multiple reminders receive one consolidated email

---

## Phase 5: User Story 3 — Recurring Task Notification Cycling (Priority: P2)

**Goal**: Recurring tasks automatically generate reminders for the next pending occurrence after the current one is completed

**Independent Test**: Create a recurring quarterly task, complete the current occurrence (which generates the next), verify a reminder is sent for the new occurrence when it's 30 days out

### Tests for User Story 3

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T037 [US3] Write test: it sends a reminder for the next pending occurrence of a recurring task after the previous occurrence is completed in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T038 [US3] Write test: it does not send reminders for inactive recurring tasks in `tests/Feature/SendMaintenanceRemindersTest.php`

### Implementation for User Story 3

- [x] T039 [US3] Verify that SendMaintenanceReminders command already handles recurring tasks correctly via the pendingOccurrence relationship query (the command queries all pending occurrences regardless of recurrence — no code changes expected, only test validation) in `app/Console/Commands/SendMaintenanceReminders.php`
- [x] T040 [US3] Run US3 tests and verify all pass via `php artisan test --compact --filter=recurring`

**Checkpoint**: Recurring task reminders verified — no additional code needed if US1 implementation correctly queries all pending occurrences

---

## Phase 6: User Story 4 — Receive Escalation Reminders (Priority: P3)

**Goal**: Users receive follow-up reminders at 7 days and 1 day before due date with increasing urgency

**Independent Test**: Create an occurrence due in 7 days (and separately 1 day), run command, verify escalation emails sent with correct urgency subject lines

### Tests for User Story 4

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T041 [US4] Write test: it sends a 7-day escalation reminder for an uncompleted occurrence due in 7 days in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T042 [US4] Write test: it sends a 1-day escalation reminder for an uncompleted occurrence due in 1 day in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T043 [US4] Write test: it does not send escalation reminders for completed occurrences in `tests/Feature/SendMaintenanceRemindersTest.php`
- [x] T044 [US4] Write test: 7-day email subject is "Reminder: {task} for {asset} due in 7 days" in `tests/Feature/MaintenanceReminderNotificationTest.php`
- [x] T045 [US4] Write test: 1-day email subject is "Urgent: {task} for {asset} due tomorrow" in `tests/Feature/MaintenanceReminderNotificationTest.php`
- [x] T046 [US4] Write test: it sends separate reminder records for 30-day and 7-day on different days for the same occurrence in `tests/Feature/SendMaintenanceRemindersTest.php`

### Implementation for User Story 4

- [x] T047 [US4] Update SendMaintenanceReminders handle() to check all three intervals (30-day, 7-day, 1-day) by iterating ReminderType cases and calculating due_date minus days for each in `app/Console/Commands/SendMaintenanceReminders.php`
- [x] T048 [US4] Update MaintenanceReminderNotification to vary subject line and urgency messaging based on ReminderType (30-day: "Upcoming", 7-day: "Reminder", 1-day: "Urgent") in `app/Notifications/MaintenanceReminderNotification.php`
- [x] T049 [US4] Update single-reminder email template to display urgency-appropriate styling and messaging based on reminder type in `resources/views/mail/maintenance-reminder.blade.php`
- [x] T050 [US4] Run US4 tests and verify all pass via `php artisan test --compact --filter=escalation`

**Checkpoint**: Escalation reminders at 7-day and 1-day intervals working with urgency-differentiated emails

---

## Phase 7: User Story 5 — Snooze/Postpone a Notification (Priority: P4)

**Goal**: Users can snooze reminders via signed URL links in emails, rescheduling the reminder for 3 or 7 days later

**Independent Test**: Generate a signed snooze URL, visit it, verify the reminder is rescheduled and the confirmation page displays

### Tests for User Story 5

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [ ] T051 [P] [US5] Create test file via `php artisan make:test ReminderSnoozeTest --pest --no-interaction` in `tests/Feature/ReminderSnoozeTest.php`
- [ ] T052 [US5] Write test: it snoozes a reminder for 7 days via signed URL and redirects to confirmation page in `tests/Feature/ReminderSnoozeTest.php`
- [ ] T053 [US5] Write test: it snoozes a reminder for 3 days via signed URL in `tests/Feature/ReminderSnoozeTest.php`
- [ ] T054 [US5] Write test: it rejects an invalid or expired signed URL with 403 in `tests/Feature/ReminderSnoozeTest.php`
- [ ] T055 [US5] Write test: it rejects snooze when resulting date would be on or after due date in `tests/Feature/ReminderSnoozeTest.php`
- [ ] T056 [US5] Write test: it increments snooze_count and clears sent_at on snooze in `tests/Feature/ReminderSnoozeTest.php`
- [ ] T057 [US5] Write test: the daily command resends a reminder when snoozed_until date arrives in `tests/Feature/SendMaintenanceRemindersTest.php`
- [ ] T058 [US5] Write test: the daily command does not resend a snoozed reminder if snoozed_until is past due date in `tests/Feature/SendMaintenanceRemindersTest.php`

### Implementation for User Story 5

- [ ] T059 [US5] Scaffold controller via `php artisan make:controller ReminderSnoozeController --no-interaction` in `app/Http/Controllers/ReminderSnoozeController.php`
- [ ] T060 [US5] Implement `snooze()` method in ReminderSnoozeController: validate days (3 or 7), validate snooze date < due date, update reminder (snoozed_until, snooze_count++, clear sent_at), redirect to confirmation in `app/Http/Controllers/ReminderSnoozeController.php`
- [ ] T061 [US5] Implement `snoozed()` method in ReminderSnoozeController to render the confirmation page in `app/Http/Controllers/ReminderSnoozeController.php`
- [ ] T062 [US5] Define signed snooze route `GET /maintenance/reminders/{reminder}/snooze/{days}` named `maintenance.reminder.snooze` with signed middleware, and confirmation route `GET /maintenance/reminders/snoozed` named `maintenance.reminder.snoozed` in `routes/reminders.php`
- [ ] T063 [US5] Create snooze confirmation Blade view with "Reminder snoozed" heading, new reminder date, and link to login in `resources/views/reminders/snoozed.blade.php`
- [ ] T064 [US5] Update MaintenanceReminderNotification to generate signed snooze URLs (3-day and 7-day) and pass them to the email template in `app/Notifications/MaintenanceReminderNotification.php`
- [ ] T065 [US5] Update single-reminder email template to include "Snooze 3 days" and "Snooze 7 days" action links in `resources/views/mail/maintenance-reminder.blade.php`
- [ ] T066 [US5] Update SendMaintenanceReminders handle() to also query for snoozed reminders where snoozed_until <= today and snoozed_until < due_date, then re-dispatch them in `app/Console/Commands/SendMaintenanceReminders.php`
- [ ] T067 [US5] Run US5 tests and verify all pass via `php artisan test --compact tests/Feature/ReminderSnoozeTest.php`

**Checkpoint**: Snooze functionality working end-to-end — signed URLs in emails, controller processes snooze, daily command handles rescheduled reminders

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Code quality, edge cases, and final validation

- [ ] T068 [P] Write ReminderType enum unit test verifying cases, values, and labels in `tests/Unit/ReminderTypeTest.php`
- [ ] T069 Write test: occurrence due in less than 30 days from creation receives the first applicable reminder (7-day or 1-day) in `tests/Feature/SendMaintenanceRemindersTest.php`
- [ ] T070 Run full test suite via `php artisan test --compact` to verify no regressions
- [ ] T071 Run `vendor/bin/pint --dirty` to fix code formatting
- [ ] T072 Run quickstart.md validation — verify all files listed exist and all environment notes are accurate

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 completion — BLOCKS all user stories
- **User Stories (Phase 3–7)**: All depend on Phase 2 completion
  - US1 (Phase 3): No dependencies on other stories
  - US2 (Phase 4): Depends on US1 (extends the command's grouping logic)
  - US3 (Phase 5): Depends on US1 (validates existing behavior with recurring data)
  - US4 (Phase 6): Depends on US1 (extends the command's interval logic)
  - US5 (Phase 7): Depends on US1 (adds snooze URLs to notification + snoozed reminder resend)
- **Polish (Phase 8)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (P1)**: Foundational → Can start immediately after Phase 2
- **US2 (P2)**: Depends on US1 (modifies the same command to add grouping)
- **US3 (P2)**: Depends on US1 (tests against existing recurring task + reminder logic)
- **US4 (P3)**: Depends on US1 (extends command and notification for multiple intervals)
- **US5 (P4)**: Depends on US1 (adds snooze fields to notification + command resend logic)

### Within Each User Story

- Tests MUST be written and FAIL before implementation (TDD)
- Models/enum before services/commands
- Commands before notifications
- Core implementation before integration
- Story complete and green before moving to next priority

### Parallel Opportunities

- T013 + T014: Test file scaffolding can run in parallel
- T028: Digest test file scaffolding is independent
- T051: Snooze test file scaffolding is independent
- T068: Enum unit test is independent of user story tests
- Within Phase 1: T001 (enum) and T002 (model scaffold) can run in parallel

---

## Parallel Example: User Story 1

```bash
# Scaffold test files in parallel:
Task T013: "Create SendMaintenanceRemindersTest test file"
Task T014: "Create MaintenanceReminderNotificationTest test file"

# Then write tests sequentially (same files):
Task T015–T023: Write individual test cases

# Then implement sequentially (dependencies between command and notification):
Task T024: Notification class
Task T025: Email template
Task T026: Command handle() logic
Task T027: Run all US1 tests
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (enum, model, migration, factory)
2. Complete Phase 2: Foundational (command skeleton, routes, schedule)
3. Complete Phase 3: User Story 1 (30-day reminder)
4. **STOP and VALIDATE**: Run tests, verify email delivery with `log` driver
5. Deploy/demo if ready — users get 30-day advance reminders

### Incremental Delivery

1. Setup + Foundational → Infrastructure ready
2. Add US1 → 30-day single reminder → Deploy (MVP!)
3. Add US2 → Digest emails → Deploy
4. Add US3 → Verify recurring tasks → Deploy
5. Add US4 → 7-day + 1-day escalation → Deploy
6. Add US5 → Snooze capability → Deploy
7. Polish → Full test suite green, Pint clean

### Sequential Recommended Order

Since all user stories extend the same command and notification classes, sequential execution (P1 → P2 → P3 → P4) is recommended to avoid merge conflicts:

1. US1 establishes the core command + notification pattern
2. US2 adds grouping/digest logic to the command
3. US3 validates recurring behavior (mostly test-only)
4. US4 extends the command for multiple intervals
5. US5 adds snooze URLs + resend logic

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- TDD is non-negotiable per constitution — write tests first, verify they fail
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Use `Notification::fake()` in tests to assert notification dispatch without sending real mail
- Use `Mail::fake()` when testing email content rendering
- All factory usage should leverage existing factories for User, Asset, MaintenanceTask, MaintenanceOccurrence
