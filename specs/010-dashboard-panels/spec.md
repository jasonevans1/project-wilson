# Feature Specification: Dashboard Overview Panels

**Feature Branch**: `010-dashboard-panels`
**Created**: 2026-03-09
**Status**: Draft
**Input**: User description: "Change the dashboard to include panels for the core features of the app. The dashboard page is the landing page of the app where users will start from."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Homeowner Orients to Current Home Status at a Glance (Priority: P1)

A homeowner logs in and immediately sees a dashboard with summary panels for each core area of Wilson: Assets, Maintenance, Service Records, and Replacement Tracking. Each panel shows the most actionable metric for that area — how many assets they manage, how many maintenance tasks are overdue or due soon, the date of their most recent service activity, and how many assets are approaching or past their replacement window. From any panel, the user can navigate directly to the corresponding feature section.

**Why this priority**: The dashboard is the landing page and the first screen users see. If it contains only placeholders, users must already know where to navigate — defeating the purpose of the landing page. Panels oriented around actionable status give every session a clear starting point.

**Independent Test**: Can be fully tested by loading the dashboard with data in the system and confirming each panel displays the correct summary metric and a working link to its feature section. Delivers immediate value as a home-status overview screen.

**Acceptance Scenarios**:

1. **Given** an authenticated user with at least one asset, maintenance task, service record, and replacement tracking configuration, **When** they navigate to the dashboard, **Then** they see four distinct panels — one each for Assets, Maintenance, Service Records, and Replacement Tracking — each showing a current status metric.
2. **Given** an authenticated user views the Assets panel, **When** the dashboard loads, **Then** the panel shows the total count of their active assets and a link to the Assets page.
3. **Given** an authenticated user views the Maintenance panel, **When** the dashboard loads, **Then** the panel shows the count of overdue tasks and upcoming tasks due within the next 7 days, plus a link to the Maintenance Schedule.
4. **Given** an authenticated user views the Service Records panel, **When** the dashboard loads, **Then** the panel shows the date and asset name of the most recently logged service record, plus a link to the Assets page where service history lives.
5. **Given** an authenticated user views the Replacement Tracking panel, **When** the dashboard loads, **Then** the panel shows how many assets are overdue for replacement or due within the next 12 months, plus a link to the Replacement Tracking page.
6. **Given** an authenticated user clicks on the link in any dashboard panel, **When** the navigation completes, **Then** they arrive at the correct feature page for that panel.

---

### User Story 2 - New User Sees Helpful Empty States (Priority: P2)

A newly registered homeowner logs in for the first time. The dashboard panels reflect that no data exists yet. Instead of blank or broken panels, each panel shows a short prompt explaining what the feature is for and a call-to-action to get started.

**Why this priority**: A new user's first experience sets the tone for adoption. Empty panels with no context are confusing and demotivating. Guided empty states reduce friction and direct users to the correct first step.

**Independent Test**: Can be fully tested with a fresh user account (no assets, tasks, service records, or replacement configurations) by confirming every panel shows an empty-state message and a working call-to-action link.

**Acceptance Scenarios**:

1. **Given** a user has no assets, **When** they view the Assets panel, **Then** the panel displays a message indicating no assets are tracked yet and a prompt to add their first asset.
2. **Given** a user has no active maintenance tasks, **When** they view the Maintenance panel, **Then** the panel displays a message indicating no tasks are scheduled and a prompt to create their first maintenance task.
3. **Given** a user has no service records, **When** they view the Service Records panel, **Then** the panel shows a message indicating no service history yet and a prompt to log their first service record.
4. **Given** a user has no replacement tracking configured, **When** they view the Replacement Tracking panel, **Then** the panel shows a message indicating no replacement timelines are set and a prompt to configure replacement tracking.

---

### Edge Cases

- What if a user has assets but no tasks, service records, or replacement configurations? Each panel operates independently — some may show real data while others show empty states.
- What if a user's most recent service record is very old (e.g., years ago)? The panel still shows the date without filtering by recency — the intent is to surface the last known activity regardless of age.
- What if a user has a large number of overdue maintenance tasks? The panel shows the count only, so any quantity is supported without layout issues.
- What happens if a user has assets with replacement tracking configured but no overdue or near-due assets? The Replacement Tracking panel shows a count of zero with a positive status message rather than an empty state.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The dashboard MUST display four summary panels, one each for: Assets, Maintenance, Service Records, and Replacement Tracking.
- **FR-002**: The Assets panel MUST display the total count of the authenticated user's active (non-archived) assets.
- **FR-003**: The Maintenance panel MUST display two counts: (1) the number of overdue task occurrences, and (2) the number of task occurrences due within the next 7 days.
- **FR-004**: The Service Records panel MUST display the service date and asset name of the most recently logged service record for the authenticated user.
- **FR-005**: The Replacement Tracking panel MUST display the count of assets whose replacement is overdue or falls within the next 12 months.
- **FR-006**: Each panel MUST include a navigation link to its corresponding feature section.
- **FR-007**: Each panel MUST display a contextual empty state — a descriptive message and a call-to-action link — when the user has no relevant data for that panel.
- **FR-008**: Dashboard data MUST be scoped strictly to the authenticated user — no cross-user data can appear.
- **FR-009**: The dashboard MUST remain functional and display appropriate empty states if a user has no data in one or more feature areas.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A returning user can assess the current status of their home (assets, maintenance, service, replacement) within 10 seconds of loading the dashboard, without navigating away from the page.
- **SC-002**: 100% of dashboard panels display accurate data matching the user's actual records — no stale, cached, or cross-user data is ever shown.
- **SC-003**: A new user with zero data sees a non-empty, actionable dashboard — every panel shows at minimum an empty-state message and a working call-to-action link.
- **SC-004**: All panel navigation links lead to the correct feature pages 100% of the time.

## Assumptions

- The four core features of Wilson are: Assets, Maintenance Schedule, Service Records, and Replacement Tracking. These are the features that warrant dedicated dashboard panels; settings and authentication pages do not.
- The "upcoming maintenance" window is 7 days. This is a reasonable default for weekly planning and avoids flooding the panel with distant future tasks.
- The "approaching replacement" window is 12 months. This matches common home-planning horizons and gives homeowners adequate lead time to budget and plan.
- Service Records are accessed from within the Assets section, so the Service Records panel links to the Assets page rather than a standalone service-record page.
- Dashboard data reflects real-time state at page load; automatic live-refresh without a full page reload is out of scope for this feature.
- Panels are informational and summary-only — no inline editing or task completion occurs on the dashboard itself.
- Panel layout and visual design follows existing Flux UI conventions and the application's established design patterns.
