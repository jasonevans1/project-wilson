# Feature Specification: Home Asset CRUD

**Feature Branch**: `001-home-asset-crud`
**Created**: 2026-02-02
**Status**: Draft
**Input**: User description: "Wilson is a web-based Home Maintenance Scheduler and Tracker that helps homeowners proactively manage the upkeep and replacement of home assets like appliances, HVAC systems, plumbing, roofing, and more. The first task is to build the Create, read, update, delete of home assets."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Add a New Home Asset (Priority: P1)

A homeowner logs into Wilson and wants to record a new asset in their home — for example, a newly installed HVAC unit or a refrigerator they just purchased. They navigate to their assets list, tap or click to add a new asset, fill in the relevant details (name, category, location, key dates), and save it. The asset then appears in their asset list.

**Why this priority**: Creating assets is the foundational action for the entire product. Without it, no other feature (maintenance scheduling, tracking, etc.) can function. This is the entry point for all asset data.

**Independent Test**: Can be fully tested by adding a single asset and confirming it persists and is visible in the asset list. Delivers the core value of recording home inventory.

**Acceptance Scenarios**:

1. **Given** an authenticated user is on the assets page, **When** they click "Add Asset" and fill in a valid asset name and category, **Then** the asset is saved and appears in their asset list.
2. **Given** an authenticated user attempts to save an asset without a name, **When** they submit the form, **Then** they see a clear validation error and the asset is not saved.
3. **Given** an authenticated user attempts to save an asset without selecting a category, **When** they submit the form, **Then** they see a clear validation error and the asset is not saved.

---

### User Story 2 - View and Browse Home Assets (Priority: P2)

A homeowner wants to see all the assets they have recorded. They navigate to their assets page and see a list of all their assets with key details at a glance (name, category, location). They can click into any asset to view its full details including dates and notes.

**Why this priority**: Viewing assets is the second most essential action — it gives the homeowner a clear inventory overview and is the gateway to updating or archiving individual assets.

**Independent Test**: Can be tested by creating one or more assets and confirming they all appear in the list with accurate summary information, and that clicking an asset shows its full detail view.

**Acceptance Scenarios**:

1. **Given** an authenticated user has one or more assets, **When** they navigate to the assets page, **Then** they see all their assets listed with name, category, and location visible.
2. **Given** an authenticated user clicks on a specific asset in the list, **When** the detail view loads, **Then** they see all fields for that asset (name, category, location, dates, notes).
3. **Given** an authenticated user has no assets, **When** they navigate to the assets page, **Then** they see an empty state with a prompt to add their first asset.

---

### User Story 3 - Update an Existing Home Asset (Priority: P3)

A homeowner needs to update details on an asset that has changed — for example, a warranty date was recorded incorrectly, or they want to add notes after a service visit. They find the asset, open it, edit the relevant fields, and save the changes.

**Why this priority**: Updates are essential for keeping asset data accurate over time, which is critical for a maintenance tracking tool. Without updates, the data becomes stale and loses value quickly.

**Independent Test**: Can be tested by creating an asset, then editing one or more of its fields and confirming the changes persist correctly.

**Acceptance Scenarios**:

1. **Given** an authenticated user is viewing an existing asset, **When** they edit a field and save, **Then** the updated values are persisted and reflected in both the detail and list views.
2. **Given** an authenticated user clears a required field (name or category) while editing, **When** they attempt to save, **Then** they see a validation error and the original value is preserved.

---

### User Story 4 - Archive and Restore a Home Asset (Priority: P4)

A homeowner removes an asset from active use (e.g., an old appliance is disposed of) and wants to move it out of their main asset list. They find the asset, choose to archive it, and confirm. The asset disappears from the default list but is preserved. Later, if needed (e.g., the asset is returned or the archive was a mistake), the homeowner can browse archived assets and restore one back to active status.

**Why this priority**: Archiving keeps the active asset list accurate and manageable while preserving historical data and any future maintenance records. It is the lowest priority CRUD operation because it is least frequently needed and can be deferred without blocking core value.

**Independent Test**: Can be tested by creating an asset, archiving it, confirming it leaves the active list, then restoring it and confirming it reappears.

**Acceptance Scenarios**:

1. **Given** an authenticated user is viewing an active asset, **When** they choose to archive it and confirm the action, **Then** the asset is marked as archived and no longer appears in the default asset list.
2. **Given** an authenticated user initiates an archive action, **When** they cancel the confirmation, **Then** the asset remains active and unchanged in their list.
3. **Given** an authenticated user toggles the view to show archived assets, **When** the list loads, **Then** all of their archived assets are displayed with a clear visual indicator that they are archived.
4. **Given** an authenticated user is viewing an archived asset, **When** they choose to restore it, **Then** the asset is set back to active status and reappears in the default asset list.

---

### Edge Cases

- What happens when a user tries to create two assets with the same name in the same category and location? (Allowed — duplicates are permitted, as a home may have multiple identical appliances.)
- How does the system handle very long text input in name or notes fields? (Input is capped at a reasonable character limit; excess input is rejected with a validation message.)
- What happens if a user attempts to access or modify another user's assets directly? (The system only ever returns or allows modification of assets belonging to the authenticated user.)
- How does the system behave when a date field is left blank? (Only date fields that are optional may be left blank; required date fields show a validation error if omitted.)
- What happens if a user tries to remove an asset that has existing maintenance records associated with it? (Assets are never permanently deleted — they are archived instead, so all associated maintenance records are preserved. Archiving is always permitted regardless of whether maintenance records exist.)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow an authenticated user to create a new home asset by providing a name, category, and location within the home.
- **FR-002**: System MUST validate that asset name and category are provided before saving; the user must see a specific error message for each missing required field.
- **FR-003**: System MUST display all of the authenticated user's assets in a browsable list view, showing name, category, and location for each asset.
- **FR-004**: System MUST provide a detail view for each asset displaying all stored fields: name, category, location, purchase date, install date, warranty expiration date, and notes.
- **FR-005**: System MUST allow an authenticated user to edit any field on an existing asset they own and persist those changes.
- **FR-006**: System MUST require confirmation before archiving an asset.
- **FR-007**: System MUST only allow users to view, create, update, or archive assets that belong to them.
- **FR-008**: System MUST support the following asset categories: Appliance, HVAC, Plumbing, Electrical, Roofing, Flooring, Exterior, and Other.
- **FR-009**: System MUST allow users to optionally record the following dates for an asset: purchase date, install date, and warranty expiration date.
- **FR-010**: System MUST allow users to optionally add free-text notes to an asset (up to 2,000 characters).
- **FR-011**: System MUST allow users to specify the location of an asset within their home (e.g., "Kitchen", "Basement") as a free-text field.
- **FR-012**: System MUST display an empty state with a clear prompt to add the first asset when a user has no active assets.
- **FR-013**: System MUST hide archived assets from the default asset list view. Archived assets MUST be viewable via a dedicated filter or toggle.
- **FR-014**: System MUST allow an authenticated user to restore an archived asset back to active status.
- **FR-015**: System MUST NOT permit permanent deletion of any home asset.

### Key Entities

- **Home Asset**: Represents a physical asset or system within a homeowner's property. Key attributes: name, category (from a defined set), location within the home, purchase date (optional), install date (optional), warranty expiration date (optional), notes (optional), and status (active or archived). Belongs to a single authenticated user.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A user can create, view, update, archive, and restore a home asset within 2 minutes each, from start to finish, with no errors.
- **SC-002**: 95% of users can successfully add their first asset on the first attempt without requiring support or guidance beyond the on-screen prompts.
- **SC-003**: All assets created by a user are visible and accurate in their list view — zero data loss or display discrepancies across create, update, and reload cycles.
- **SC-004**: The system correctly isolates user data — no user can view or modify another user's assets under any circumstance.
- **SC-005**: Validation prevents saving incomplete or invalid asset data 100% of the time, with clear and specific error messages for each violation.

## Assumptions

- Each authenticated user manages their own set of assets independently. Multi-user or shared-home scenarios (e.g., two spouses managing the same home) are out of scope for this feature.
- Asset categories are a fixed, predefined list for v1: Appliance, HVAC, Plumbing, Electrical, Roofing, Flooring, Exterior, and Other. User-defined custom categories are out of scope.
- Location within the home is a free-text field rather than a structured selector, to keep initial scope small and avoid the complexity of modeling home layouts.
- Date fields (purchase, install, warranty expiration) are all optional. Only asset name and category are required to create an asset.
- Notes are a single free-text block (up to 2,000 characters) rather than a structured or timestamped field.
- Assets are never permanently deleted. Archiving is the only way to remove an asset from the active list; archived assets are always preserved and can be restored. This protects any future maintenance records that may be associated with an asset.
- The application already has a working user authentication and registration system; this feature builds on top of that existing auth infrastructure.
