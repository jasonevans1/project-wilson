# Feature Specification: Replacement Tracking

**Feature Branch**: `007-replacement-tracking`
**Created**: 2026-03-04
**Status**: Draft
**Input**: User description: "Replacement Tracking — track end-of-life timelines for home assets and plan proactive replacements"

## Clarifications

### Session 2026-03-04

- Q: How should unconfigured assets appear on the replacement tracking dashboard? → A: Show all assets in a single unified list; unconfigured assets display a "Set up tracking" call-to-action inline rather than being hidden or grouped separately.
- Q: What clears the overdue notification state so it can fire again on a future cycle? → A: Both: the overdue state clears automatically when a replacement event is recorded, OR when the user explicitly dismisses/acknowledges the overdue notification—whichever comes first.
- Q: When recording a replacement, can the user also update the expected lifespan on the same form? → A: Yes — the "Record Replacement" form includes an optional lifespan field that defaults to the current value and can be changed in the same step.
- Q: Where does the replacement tracking dashboard live in the app's navigation? → A: Dedicated page in the main navigation (e.g., a "Replacement Tracking" nav link), not embedded in the assets list.
- Q: Where on the asset detail page does a user configure replacement tracking? → A: A dedicated "Replacement" tab or collapsible section on the existing asset detail page (not a modal-only or separate sub-page).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Configure Replacement Lifespan for an Asset (Priority: P1)

A homeowner opens an asset's detail page and navigates to the dedicated "Replacement" tab or section. The system applies a default lifespan value based on the asset type (e.g., 9 years for a dishwasher) which the user can accept or override. The user also confirms or enters the installation date. The system immediately displays the calculated replacement year and remaining useful life.

**Why this priority**: This is the foundational data-entry workflow. Without lifespan data, no replacement timelines, visualizations, or alerts can exist. All other stories depend on this one.

**Independent Test**: Can be fully tested by opening any asset, saving a lifespan and installation date, and verifying the replacement year and remaining-life percentage are computed and displayed correctly.

**Acceptance Scenarios**:

1. **Given** an asset with no lifespan configured, **When** the user opens the asset and views the replacement tracking section, **Then** the system pre-populates a suggested lifespan based on the asset type (or leaves blank if no standard exists).
2. **Given** an asset with a suggested lifespan displayed, **When** the user accepts the default and saves, **Then** the expected replacement date is calculated as installation date + lifespan and is shown to the user.
3. **Given** an asset with a suggested lifespan displayed, **When** the user overrides the value with a custom number of years and saves, **Then** the custom lifespan is stored and the replacement date recalculates accordingly.
4. **Given** an asset with lifespan and installation date saved, **When** the user views the asset, **Then** the system shows remaining years (e.g., "7.2 years remaining") and a useful-life percentage.
5. **Given** an asset whose calculated replacement date is in the past, **When** the user views the asset, **Then** the system indicates the asset is past its expected replacement date (e.g., "Past due by 1.3 years").

---

### User Story 2 - View Replacement Dashboard / Overview (Priority: P2)

A homeowner navigates to a dedicated "Replacement Tracking" page from the main navigation. The page shows a unified list of all assets. Assets with replacement tracking configured are sorted by urgency (soonest to be replaced first) and show the asset name, expected replacement year, remaining life percentage, and a visual progress bar. Assets past their expected replacement date are highlighted. Assets without replacement tracking configured appear below the tracked assets with an inline "Set up tracking" call-to-action.

**Why this priority**: After data entry, visibility across all assets is the next highest-value capability—it enables the budget planning and repair-vs-replace decisions called out in the business value statement.

**Independent Test**: Can be fully tested by configuring lifespan data on several assets with varying remaining-life values and verifying the dashboard lists them sorted by urgency with correct remaining-life values, and that untracked assets appear with a "Set up tracking" prompt.

**Acceptance Scenarios**:

1. **Given** multiple assets with replacement tracking configured, **When** the user navigates to the replacement tracking dashboard, **Then** tracked assets are listed ordered from soonest expected replacement to latest.
2. **Given** the dashboard is displayed, **When** an asset has less than 1 year remaining, **Then** it is visually distinguished (e.g., highlighted or flagged) from assets with more time remaining.
3. **Given** the dashboard is displayed, **When** an asset is past its expected replacement date, **Then** it appears at the top of the list and is clearly marked as overdue.
4. **Given** an asset with no replacement tracking data configured, **When** the user views the dashboard, **Then** that asset appears in the same list with an inline "Set up tracking" call-to-action, positioned below all tracked assets.

---

### User Story 3 - Record an Asset Replacement (Priority: P3)

When a homeowner replaces an asset, they log the replacement event by entering the new installation date, an optional replacement cost, and optional notes (e.g., brand, contractor). The form also exposes the current expected lifespan as an editable field (pre-filled with the existing value) so the user can update it in the same step if the replacement unit has a different expected life. The system resets the replacement timeline using the recorded installation date and the lifespan value saved on the form.

**Why this priority**: Capturing the replacement history completes the lifecycle loop and supports insurance, warranty, and home-sale documentation goals. It is lower priority than configuring and viewing timelines since the dashboard delivers value without historical records.

**Independent Test**: Can be fully tested by recording a replacement on an asset, then verifying the remaining-life counter resets from the new installation date and the historical record is accessible.

**Acceptance Scenarios**:

1. **Given** an asset with replacement tracking configured, **When** the user selects "Record Replacement" and enters a new installation date and cost, **Then** the system saves the replacement record and recalculates the replacement timeline from the new installation date.
2. **Given** a replacement has been recorded, **When** the user views the asset, **Then** the replacement history (date replaced, cost) is accessible.
3. **Given** a replacement record being created, **When** the user omits the replacement cost, **Then** the record is still saved and cost is shown as "Not recorded."

---

### User Story 4 - Replacement Alerts / Notifications (Priority: P4)

The system notifies the homeowner when an asset is approaching its expected replacement date. Alerts fire at configurable thresholds (default: 2 years out and 1 year out). Users can opt out per-asset or globally in notification settings.

**Why this priority**: Proactive alerts are the automation layer that prevents surprise failures. They are valuable but require US1–US2 to be in place first, and notification infrastructure already exists in the project.

**Independent Test**: Can be fully tested by setting an asset's replacement date to within the alert threshold and verifying that a notification is generated with correct asset details.

**Acceptance Scenarios**:

1. **Given** an asset whose expected replacement date is within 2 years, **When** the system's scheduled check runs, **Then** a replacement-approaching notification is created for that asset.
2. **Given** an asset whose expected replacement date is within 1 year, **When** the system's scheduled check runs, **Then** an escalated replacement-approaching notification is created.
3. **Given** a user who has opted out of replacement alerts for a specific asset, **When** the threshold is crossed, **Then** no notification is created for that asset.
4. **Given** an asset already past its expected replacement date, **When** the scheduled check runs, **Then** an overdue replacement notification is created (only once per overdue cycle; the cycle resets when the user records a replacement event or explicitly dismisses the overdue notification).

---

### Edge Cases

- What happens when an asset has no installation date set? The system cannot calculate a replacement date and should prompt the user to enter one before showing timeline data.
- What happens when a user enters an installation date in the future? The system should reject it with a validation error.
- What happens when lifespan is set to 0 or a negative number? The system should reject it with a validation error (minimum 1 year).
- How does the system handle an asset that has been replaced multiple times? All past replacements are retained in history; the most recent replacement's installation date drives the current timeline.
- What if the asset type has no industry-standard lifespan? The default field is left blank and the user must enter a value manually.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST display a suggested default lifespan for an asset based on its asset type when the user opens replacement tracking for that asset.
- **FR-002**: Users MUST be able to enter or override the expected lifespan (in whole years) for any asset.
- **FR-003**: Users MUST be able to enter or update the installation date for any asset via a dedicated "Replacement" tab or collapsible section on the asset detail page.
- **FR-004**: System MUST automatically calculate the expected replacement date as installation date + expected lifespan whenever either value changes.
- **FR-005**: System MUST display remaining useful life as both a human-readable duration (e.g., "7.2 years remaining") and a percentage of total lifespan consumed.
- **FR-006**: System MUST display a visual progress bar indicating the percentage of useful life consumed.
- **FR-007**: System MUST clearly indicate when an asset is past its expected replacement date.
- **FR-008**: System MUST provide a dedicated "Replacement Tracking" page accessible from the main navigation. The page MUST list all assets in a unified list: tracked assets ordered by urgency (soonest replacement first), followed by untracked assets each showing an inline "Set up tracking" call-to-action.
- **FR-009**: System MUST visually distinguish assets with less than 1 year of remaining useful life on the dashboard.
- **FR-010**: Users MUST be able to record a replacement event for an asset, capturing: new installation date (required), optional replacement cost, optional notes, and an optional updated expected lifespan (pre-filled with the current value).
- **FR-011**: System MUST reset the replacement timeline from the new installation date when a replacement event is recorded.
- **FR-012**: System MUST retain the full history of replacement events for each asset (date, cost, optional notes).
- **FR-013**: System MUST generate a notification when an asset crosses the 2-year-remaining threshold.
- **FR-014**: System MUST generate an escalated notification when an asset crosses the 1-year-remaining threshold.
- **FR-015**: System MUST generate an overdue notification when an asset passes its expected replacement date (generated once per overdue cycle). The overdue cycle resets—allowing a future overdue notification—when the user records a replacement event OR explicitly dismisses the overdue notification, whichever comes first.
- **FR-015a**: Users MUST be able to explicitly dismiss an overdue replacement notification from within the notification interface.
- **FR-016**: Users MUST be able to opt out of replacement alerts per asset or globally via notification settings.
- **FR-017**: System MUST reject an installation date set in the future with a clear validation error.
- **FR-018**: System MUST reject a lifespan value of less than 1 year with a clear validation error.

### Key Entities

- **Asset Lifespan Profile**: Ties an expected lifespan (years) and an installation date to an asset; drives replacement date calculation. Belongs to a single asset.
- **Replacement Event**: A recorded instance of an asset being replaced—captures installation date, optional cost, optional notes, and timestamp. Belongs to a single asset; multiple records per asset form its replacement history.
- **Asset Type Lifespan Standard**: A reference table of default expected lifespans keyed by asset type (e.g., Dishwasher → 9 years, Asphalt Roof → 17 years). Read-only reference data; not user-editable.
- **Replacement Notification**: A notification record generated when an asset crosses a replacement-approaching or overdue threshold. Relates to existing notification infrastructure.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can configure replacement tracking (lifespan + installation date) for an asset in under 60 seconds.
- **SC-002**: The replacement tracking dashboard loads and displays all assets (tracked and untracked) in under 2 seconds regardless of asset count.
- **SC-003**: 100% of assets with a calculated replacement date within 2 years receive at least one replacement-approaching notification (when notifications are enabled).
- **SC-004**: Users can record a replacement event and see the updated timeline in under 60 seconds.
- **SC-005**: Replacement history is accessible for every asset that has had at least one replacement recorded, with no data loss.
- **SC-006**: Default lifespan values are present for all asset types already defined in the system (no "unknown" gaps for built-in types).

## Assumptions

- The existing `HomeAsset` model (from 001-home-asset-crud) provides the asset entity this feature extends; no new top-level asset concept is needed.
- Asset types are already categorized in the system (from 002-asset-template-system); default lifespans will be mapped to those existing categories.
- The notification infrastructure (from 006-notification-system) can be extended to support replacement notification types without architectural changes.
- "Brand/Quality Factor" lifespan adjustment is out of scope for this iteration; the user can achieve the same result by manually overriding the expected lifespan.
- Replacement cost is stored as a decimal number in the user's local currency; no currency conversion is required.
- Alert thresholds (2 years, 1 year) are system-wide defaults; per-user threshold customization is out of scope.
- Scheduled replacement checks run once daily.
