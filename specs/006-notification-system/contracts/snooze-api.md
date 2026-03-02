# API Contract: Snooze Reminder

**Branch**: `006-notification-system` | **Date**: 2026-02-28

## Snooze a Maintenance Reminder

Allows a user to postpone a reminder via a signed URL in their email. No authentication required — the signed URL provides security.

### Endpoint

```
GET /maintenance/reminders/{reminder}/snooze/{days}
```

**Route name**: `maintenance.reminder.snooze`

**Signed URL**: Yes — uses Laravel's signed URL middleware to prevent tampering.

### Parameters

| Parameter | Type | Location | Required | Description |
|-----------|------|----------|----------|-------------|
| reminder | integer | path | yes | MaintenanceReminder ID |
| days | integer | path | yes | Snooze duration (3 or 7) |
| signature | string | query | yes | Laravel signed URL signature (auto-generated) |

### Validation

- `days` must be one of: `3`, `7`
- The signed URL must be valid (not expired, not tampered)
- The reminder must exist and belong to the user
- The resulting snooze date must be before the occurrence's due date

### Responses

| Status | Condition | Response |
|--------|-----------|----------|
| 302 | Success | Redirect to confirmation page with success flash message |
| 403 | Invalid/expired signature | Error page: "This link has expired or is invalid" |
| 404 | Reminder not found | Standard 404 |
| 422 | Snooze date would be on or after due date | Redirect back with error: "Cannot snooze past the due date" |

### Confirmation Page

After a successful snooze, the user is redirected to a simple confirmation page showing:
- "Reminder snoozed" heading
- The new reminder date
- A link to log in to Wilson

**Route**: `GET /maintenance/reminders/snoozed`
**Route name**: `maintenance.reminder.snoozed`

### Example Signed URL (in email)

```
https://wilson.example.com/maintenance/reminders/42/snooze/7?signature=abc123...
```

### Business Logic

1. Validate the signed URL signature
2. Find the MaintenanceReminder by ID
3. Validate the snooze period won't exceed the due date
4. Update the reminder: set `snoozed_until` = today + days, increment `snooze_count`, clear `sent_at`
5. Redirect to confirmation page
