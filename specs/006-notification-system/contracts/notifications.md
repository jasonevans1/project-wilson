# Contract: Notification Classes

**Branch**: `006-notification-system` | **Date**: 2026-02-28

## Notification Classes

### MaintenanceReminderNotification

Sent when a single maintenance task triggers a reminder for a user.

**Channel**: mail (queued via `ShouldQueue`)

**Data provided to template**:

| Field | Type | Source |
|-------|------|--------|
| user_name | string | User.name |
| asset_name | string | Asset.name |
| task_name | string | MaintenanceTask.name |
| task_description | string | MaintenanceTask.description |
| due_date | Carbon date | MaintenanceOccurrence.due_date |
| urgency | ReminderType enum | 30_day, 7_day, or 1_day |
| task_url | string | Signed URL to maintenance task in Wilson |
| snooze_3_url | string | Signed URL for 3-day snooze |
| snooze_7_url | string | Signed URL for 7-day snooze |

**Email subject lines by urgency**:
- 30-day: "Upcoming maintenance: {task_name} for {asset_name}"
- 7-day: "Reminder: {task_name} for {asset_name} due in 7 days"
- 1-day: "Urgent: {task_name} for {asset_name} due tomorrow"

### MaintenanceDigestNotification

Sent when multiple maintenance tasks trigger reminders for the same user on the same day.

**Channel**: mail (queued via `ShouldQueue`)

**Data provided to template**:

| Field | Type | Source |
|-------|------|--------|
| user_name | string | User.name |
| reminders | Collection | Array of reminder data (see below) |
| reminder_count | integer | Number of tasks in digest |

**Each item in `reminders` collection**:

| Field | Type | Source |
|-------|------|--------|
| asset_name | string | Asset.name |
| task_name | string | MaintenanceTask.name |
| due_date | Carbon date | MaintenanceOccurrence.due_date |
| urgency | ReminderType enum | 30_day, 7_day, or 1_day |
| task_url | string | URL to maintenance task in Wilson |

**Email subject**: "Wilson: You have {count} upcoming maintenance tasks"

**Note**: Digest emails do not include snooze links (user should visit Wilson to manage individual tasks).

## Scheduled Command

### SendMaintenanceReminders (Artisan Command)

**Signature**: `maintenance:send-reminders`
**Schedule**: Daily
**Registered in**: `routes/console.php`

**Logic**:
1. Find all pending MaintenanceOccurrences where:
   - `completed_at` is null
   - Parent MaintenanceTask `is_active` is true
   - Parent User `email_verified_at` is not null
   - `due_date` minus 30/7/1 days <= today
2. For each occurrence + interval, check if a MaintenanceReminder record already exists with `sent_at` not null
3. Also check for snoozed reminders where `snoozed_until` <= today
4. Create MaintenanceReminder records for new notifications
5. Group all pending reminders by user
6. For each user:
   - If 1 reminder → dispatch `MaintenanceReminderNotification`
   - If > 1 reminders → dispatch `MaintenanceDigestNotification`
7. Update `sent_at` on all dispatched reminders

## Queued Job

### ProcessMaintenanceReminder

**Queue**: default (database driver)
**Implements**: `ShouldQueue`
**Purpose**: Wraps the notification dispatch to ensure mail sending is queued and retryable.
