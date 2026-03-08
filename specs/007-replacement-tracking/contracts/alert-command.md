# Contract: SendReplacementAlerts Command

**Branch**: `007-replacement-tracking` | **Date**: 2026-03-04

---

## Command Signature

```
replacement:send-alerts
```

**Description**: Send replacement approaching and overdue alert notifications for home assets.

**Schedule**: Daily, registered in `routes/console.php`.

---

## Logic Contract

### Input

All user assets that:
1. Have both `install_date` and `expected_lifespan_years` set (i.e., are "tracked").
2. Belong to a user where `email_verified_at IS NOT NULL` AND `replacement_alerts_enabled = true`.
3. Have `replacement_alerts_enabled = true` on the asset itself.

### Per-Asset Processing

For each tracked, opted-in asset, compute `days_remaining = expected_replacement_date - today()`.

| Condition | Alert Type | Action |
|-----------|-----------|--------|
| `days_remaining <= 730` (≤ 2 years) AND `days_remaining > 365` | `TwoYear` | Send if no sent, non-dismissed `AssetReplacementAlert` row exists |
| `days_remaining <= 365` (≤ 1 year) AND `days_remaining > 0` | `OneYear` | Send if no sent, non-dismissed row exists |
| `days_remaining <= 0` (overdue) | `Overdue` | Send if no sent, non-dismissed row exists |

### Deduplication

Before sending, check for an existing `AssetReplacementAlert` row with `(asset_id, alert_type)`:
- If row exists AND `sent_at IS NOT NULL` AND `dismissed_at IS NULL` → **skip** (already sent, not cleared).
- If row exists AND `dismissed_at IS NOT NULL` → **skip** (user dismissed this overdue cycle).
- If row does not exist or `sent_at IS NULL` → **send and record**.

### Notification Class

```
App\Notifications\ReplacementAlertNotification
```

Constructor accepts `Asset $asset` and `ReplacementAlertType $alertType`.

The notification:
- Implements `ShouldQueue`.
- Uses the `mail` channel.
- For `Overdue` type: includes a signed URL link to dismiss the alert.
- Subject lines:
  - `TwoYear`: "{Asset Name} — Replacement Due in ~2 Years"
  - `OneYear`: "{Asset Name} — Replacement Due Soon"
  - `Overdue`: "{Asset Name} — Past Expected Replacement Date"

### After Sending

Create or update `AssetReplacementAlert`:
```
asset_id = $asset->id
alert_type = $alertType
sent_at = now()
dismissed_at = null
```

### Return Codes

| Code | Meaning |
|------|---------|
| 0 (SUCCESS) | Command ran to completion |
| 1 (FAILURE) | Unexpected exception (logged) |

---

## Dismissal Route Contract

**Route**: `GET /replacement-tracking/alerts/{alert}/dismiss`
**Name**: `replacement.alert.dismiss`
**Type**: Signed URL (expiry: 30 days from send date)
**Controller**: `App\Http\Controllers\ReplacementAlertDismissController`

### Handler Logic

1. Validate signed URL (Laravel middleware `signed`).
2. Find `AssetReplacementAlert` by `{alert}` id.
3. Verify asset belongs to authenticated or guest user matching the alert's asset owner (signed URL provides implicit authorization).
4. Set `dismissed_at = now()`.
5. Redirect to `/replacement-tracking` with a success flash message.

### Error States

| Condition | Response |
|-----------|---------|
| Invalid/expired signature | 403 |
| Alert already dismissed | Redirect with info flash (idempotent) |
| Alert not found | 404 |
