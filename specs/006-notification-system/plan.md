# Implementation Plan: Notification System

**Branch**: `006-notification-system` | **Date**: 2026-02-28 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/006-notification-system/spec.md`

## Summary

Deliver proactive email reminders for upcoming home maintenance tasks. The system sends 30-day advance reminders (with 7-day and 1-day escalations) via a daily scheduled job. Multiple reminders for the same user are consolidated into digest emails. Users can snooze reminders via signed URLs in emails. A new `maintenance_reminders` table tracks sent/snoozed state to prevent duplicates and enable catch-up after missed runs.

## Technical Context

**Language/Version**: PHP 8.3
**Primary Dependencies**: Laravel 12, Livewire 4
**Storage**: MariaDB 10.11 (via Eloquent — 1 new table: `maintenance_reminders`)
**Testing**: Pest 4
**Target Platform**: Linux server (DDEV local dev)
**Project Type**: Web application (Laravel monolith)
**Performance Goals**: Process all pending notifications for up to 10,000 users within 5 minutes
**Constraints**: Queued mail delivery (database queue driver), signed URLs for email actions
**Scale/Scope**: 1 new model, 2 notification classes, 1 command, 1 controller, 1 enum, email templates

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] I. Modularity: each new module has a single, declared purpose
      - MaintenanceReminder model: tracks reminder state
      - ReminderType enum: defines reminder intervals
      - SendMaintenanceReminders command: daily scheduling logic
      - Notification classes: email content generation (one per format)
      - ReminderSnoozeController: handles snooze signed URLs
- [x] II. Separation of Concerns: business logic lives outside controllers
      and Livewire views; validation is in Form Requests
      - Core logic lives in the SendMaintenanceReminders command and notification classes
      - Snooze controller is thin (validate signed URL, update model, redirect)
      - No Livewire components needed for this feature
- [x] III. High Cohesion: related domain logic is grouped; no cross-domain
      files added without justification
      - All new files are in existing Laravel directories (Models, Notifications, Console/Commands, Controllers)
      - ReminderType enum in App\Enums alongside existing enums
      - Email templates in resources/views/mail/
- [x] IV. Information Hiding: new public interfaces are minimal; internal
      helpers are protected/private; all types are declared
      - MaintenanceReminder model exposes only necessary relationships and scopes
      - Notification classes have typed constructors and return typed MailMessage
      - All methods have explicit return types
- [x] V. Appropriate Coupling: dependency direction flows inward
      (domain ← application ← infrastructure); no circular dependencies;
      eager loading applied where relationships are traversed
      - Reminders → Occurrences → Tasks → Assets (one-directional)
      - Eager loading: `with(['task.asset', 'task.user'])` on occurrence queries
      - Notifications implement ShouldQueue for I/O-heavy mail sends

## Project Structure

### Documentation (this feature)

```text
specs/006-notification-system/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 research decisions
├── data-model.md        # Phase 1 data model
├── quickstart.md        # Phase 1 implementation guide
├── contracts/           # Phase 1 API contracts
│   ├── snooze-api.md    # Snooze endpoint contract
│   └── notifications.md # Notification class contracts
└── tasks.md             # Phase 2 output (created by /speckit.tasks)
```

### Source Code (repository root)

```text
app/
├── Console/Commands/
│   └── SendMaintenanceReminders.php    # Daily scheduled command
├── Enums/
│   └── ReminderType.php                # 30_day, 7_day, 1_day enum
├── Http/Controllers/
│   └── ReminderSnoozeController.php    # Signed URL snooze handler
├── Models/
│   └── MaintenanceReminder.php         # Reminder tracking model
└── Notifications/
    ├── MaintenanceReminderNotification.php  # Single-task email
    └── MaintenanceDigestNotification.php    # Multi-task digest email

database/
├── factories/
│   └── MaintenanceReminderFactory.php  # Test factory
└── migrations/
    └── xxxx_xx_xx_create_maintenance_reminders_table.php

resources/views/
└── mail/
    ├── maintenance-reminder.blade.php  # Single reminder template
    └── maintenance-digest.blade.php    # Digest template

routes/
├── console.php          # + Schedule daily command
├── reminders.php        # Snooze routes (new file)
└── web.php              # + Include reminders.php

tests/
├── Feature/
│   ├── SendMaintenanceRemindersTest.php
│   ├── ReminderSnoozeTest.php
│   ├── MaintenanceReminderNotificationTest.php
│   └── MaintenanceDigestNotificationTest.php
└── Unit/
    └── ReminderTypeTest.php
```

**Structure Decision**: Follows the existing Laravel 12 streamlined directory layout. All new files go into standard Laravel directories. No new top-level directories needed.

## Complexity Tracking

> No constitution violations. All design decisions use standard Laravel patterns.
