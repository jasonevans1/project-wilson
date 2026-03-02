# Data Model: Notification System

**Branch**: `006-notification-system` | **Date**: 2026-02-28

## New Entities

### MaintenanceReminder

Tracks which reminders have been sent to prevent duplicates and enable snooze functionality.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| user_id | bigint | FK → users.id, cascade delete, not null | The user who receives the reminder |
| maintenance_occurrence_id | bigint | FK → maintenance_occurrences.id, cascade delete, not null | The occurrence this reminder is for |
| reminder_type | string (enum) | not null | One of: `30_day`, `7_day`, `1_day` |
| sent_at | timestamp | nullable | When the reminder email was actually sent |
| snoozed_until | date | nullable | If snoozed, the date to resend the reminder |
| snooze_count | unsigned integer | default: 0 | Number of times this reminder has been snoozed |
| created_at | timestamp | auto | Record creation timestamp |
| updated_at | timestamp | auto | Record update timestamp |

**Unique constraint**: `(maintenance_occurrence_id, reminder_type)` — ensures only one reminder per occurrence per interval.

**Indexes**:
- `(user_id, sent_at)` — for querying unsent reminders grouped by user
- `(snoozed_until)` — for finding reminders ready to be resent after snooze

### ReminderType Enum

| Case | Value | Label |
|------|-------|-------|
| ThirtyDay | `30_day` | 30-Day Reminder |
| SevenDay | `7_day` | 7-Day Reminder |
| OneDay | `1_day` | 1-Day Reminder |

## Existing Entities (Referenced, Not Modified)

### MaintenanceOccurrence (existing)

| Field | Relevant Usage |
|-------|---------------|
| id | Referenced by MaintenanceReminder.maintenance_occurrence_id |
| due_date | Used to calculate reminder send dates (due_date - 30, - 7, - 1) |
| completed_at | If not null, skip all reminders for this occurrence |
| maintenance_task_id | Used to check if parent task is active |

### MaintenanceTask (existing)

| Field | Relevant Usage |
|-------|---------------|
| is_active | If false, skip all reminders for this task's occurrences |
| asset_id | Used to include asset name in reminder emails |
| name | Included in reminder email content |
| description | Included in reminder email content |

### User (existing)

| Field | Relevant Usage |
|-------|---------------|
| id | Referenced by MaintenanceReminder.user_id |
| email | Notification delivery address |
| email_verified_at | Must not be null to receive reminders |
| name | Used in email greeting |

### Asset (existing)

| Field | Relevant Usage |
|-------|---------------|
| name | Included in reminder email content |

## Relationships

```
User (1) ──→ (Many) MaintenanceReminder
MaintenanceOccurrence (1) ──→ (Many) MaintenanceReminder
  (constrained: max 1 per reminder_type via unique index)

MaintenanceReminder ──→ MaintenanceOccurrence ──→ MaintenanceTask ──→ Asset
  (traversal path for email content: reminder → occurrence → task → asset)
```

## State Transitions

### MaintenanceReminder Lifecycle

```
[Created]  →  sent_at = null, snoozed_until = null
     │
     ├──→ [Sent]  →  sent_at = timestamp
     │         │
     │         └──→ [Snoozed]  →  snoozed_until = date, snooze_count++, sent_at = null
     │                   │
     │                   ├──→ [Re-sent]  →  sent_at = timestamp, snoozed_until = null
     │                   │
     │                   └──→ [Cancelled]  →  snoozed_until >= due_date (no resend)
     │
     └──→ [Skipped]  →  occurrence completed before send date (never created)
```

## Validation Rules

| Entity | Field | Rules |
|--------|-------|-------|
| MaintenanceReminder | user_id | required, exists:users,id |
| MaintenanceReminder | maintenance_occurrence_id | required, exists:maintenance_occurrences,id |
| MaintenanceReminder | reminder_type | required, valid ReminderType enum value |
| MaintenanceReminder | snoozed_until | nullable, date, must be before occurrence due_date |
