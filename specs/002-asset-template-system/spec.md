# Feature Specification: Asset Template System

**Feature Branch**: `002-asset-template-system`
**Created**: 2026-02-16
**Status**: Draft
**Input**: User description: "The next task is to build a template system. Users can add items individually or use pre-built templates to reduce the time it takes a user to do the data entry. Pre-built templates for common home items. Template selection and customization. Template-to-asset conversion workflow. Template library management."

## Clarifications

### Session 2026-02-16

- Q: Should the system indicate when a template matches an item the user already owns? → A: Visual indicator during browsing — templates matching an existing asset name show a subtle "already owned" badge.
- Q: Should the empty state (no assets) promote the template flow for new users? → A: Promote templates — empty state highlights templates as the recommended way to get started, with manual add as secondary option.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Browse and Select Templates (Priority: P1)

A homeowner wants to quickly add common home items without manually filling in every field. They navigate to their assets page, choose to add from templates, and see a library of pre-built templates organized by group (e.g., "Kitchen", "Bathroom", "HVAC & Climate"). Each template represents a single common home item with sensible defaults pre-filled (name, category, location). The user selects one or more templates they want to add.

**Why this priority**: Template browsing and selection is the foundation of the entire template system. Without the ability to discover and pick templates, the rest of the feature cannot function.

**Independent Test**: Can be fully tested by browsing the template library, selecting a template, and confirming it is added to a review/customization step. Delivers the core value of reducing data entry time.

**Acceptance Scenarios**:

1. **Given** an authenticated user is on the assets page, **When** they choose to add from templates, **Then** they see a library of pre-built templates organized by group.
2. **Given** an authenticated user is browsing the template library, **When** they view a template group (e.g., "Kitchen"), **Then** they see all individual item templates within that group with pre-filled details visible.
3. **Given** an authenticated user is browsing templates, **When** they select one or more templates, **Then** the selected templates are visually indicated and a count of selected items is shown.
4. **Given** an authenticated user is browsing templates and already owns an asset with the same name as a template, **When** they view that template, **Then** it displays a subtle "already owned" indicator to inform the user.

---

### User Story 2 - Customize and Convert Templates to Assets (Priority: P2)

After selecting templates, the homeowner is presented with a review step where they can customize each selected template before it becomes a real asset. They can edit any pre-filled field (name, category, location, dates, notes), remove items they no longer want, or keep the defaults as-is. Once satisfied, they confirm and all selected items are converted into assets in their inventory.

**Why this priority**: Customization before conversion is critical because every home is different. A "Kitchen Refrigerator" template might need to be renamed or have a purchase date added. Without this step, templates would create inaccurate asset records.

**Independent Test**: Can be tested by selecting a template, modifying its pre-filled fields, confirming the conversion, and verifying the resulting asset has the customized values.

**Acceptance Scenarios**:

1. **Given** an authenticated user has selected one or more templates, **When** they proceed to the review step, **Then** they see each selected template with its pre-filled fields editable.
2. **Given** an authenticated user is reviewing selected templates, **When** they edit a field on a template item (e.g., change the name or add a purchase date), **Then** the change is reflected in the review and carried through to the created asset.
3. **Given** an authenticated user is reviewing selected templates, **When** they remove a template item from the review, **Then** it is excluded from the conversion and no asset is created for it.
4. **Given** an authenticated user confirms the conversion, **When** the assets are created, **Then** all converted items appear in their asset list with the correct field values (default or customized).
5. **Given** an authenticated user confirms the conversion with multiple templates, **When** the assets are created, **Then** all items are created as separate active assets, and the user sees a confirmation of how many assets were added.

---

### User Story 3 - Browse Templates by Group (Priority: P3)

A homeowner wants to find templates relevant to a specific part of their home or a specific type of system. They can browse the template library by group (e.g., "Kitchen", "Bathroom", "HVAC & Climate", "Outdoor & Exterior"). Each group contains a curated set of item templates commonly found in that context.

**Why this priority**: Organized browsing makes the template library usable at scale. As the library grows, users need a way to quickly find relevant templates without scrolling through the entire list.

**Independent Test**: Can be tested by navigating to a specific template group and confirming only the relevant templates are displayed.

**Acceptance Scenarios**:

1. **Given** an authenticated user is in the template library, **When** they browse by group, **Then** they see all available template groups with a count of items in each.
2. **Given** an authenticated user selects a template group, **When** the group expands or filters, **Then** only templates belonging to that group are displayed.
3. **Given** an authenticated user has items selected from one group, **When** they browse to a different group, **Then** their previous selections are preserved.

---

### Edge Cases

- What happens when a user tries to convert a template but their customized values fail validation (e.g., they clear the required name field)? The system shows validation errors on the specific item and field, and no assets are created until all items pass validation.
- What happens if a user selects the same template twice? Each selection creates an independent copy in the review step, allowing the user to customize each one differently (e.g., two "Bathroom Sink" assets for different bathrooms).
- What happens when new templates are added to the system library? They become available to all users immediately without any action required.
- What happens if a user navigates away during the review/customization step? Unsaved selections are lost; the user must re-select templates. No partial assets are created.
- Can users create their own custom templates? No — templates are system-managed in this version. Users add custom items via the existing manual asset creation flow.
- What does the empty state look like for new users now that templates exist? The empty state promotes the template flow as the primary action with a clear call-to-action, and offers manual asset creation as a secondary option.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a template library containing pre-built templates for common home items, accessible from the asset management area.
- **FR-002**: System MUST organize templates into logical groups (e.g., by room or system type) to aid discovery.
- **FR-003**: Each template MUST include pre-filled values for at minimum: name, category (matching the existing asset categories), and a suggested location.
- **FR-004**: System MUST allow users to select one or more templates from the library in a single session.
- **FR-005**: System MUST provide a review/customization step after template selection where users can edit any pre-filled field before conversion.
- **FR-006**: System MUST allow users to remove individual items from the review step before conversion.
- **FR-007**: System MUST convert confirmed templates into standard home assets (as defined in 001-home-asset-crud) with all the same validation rules applied.
- **FR-008**: System MUST display the count of selected templates during the selection process.
- **FR-009**: System MUST show a confirmation summary after successful conversion, indicating how many assets were created.
- **FR-010**: System MUST preserve template selections when users navigate between template groups during selection.
- **FR-011**: System MUST apply the same validation rules to template-derived assets as manually created assets (name required, category required, location required, etc.).
- **FR-012**: Templates MUST be system-managed (not user-created or user-editable). The template library is read-only to end users.
- **FR-013**: System MUST ship with a seed set of templates covering common items across major home areas (kitchen, bathroom, bedroom, living areas, HVAC, exterior, laundry, garage).
- **FR-014**: System MUST allow selecting the same template multiple times, with each selection treated as an independent item in the review step.
- **FR-015**: System MUST display a subtle "already owned" indicator on templates whose name matches an existing asset in the user's inventory. This indicator is informational only and does not block selection.
- **FR-016**: System MUST update the empty state (when a user has no assets) to promote the template flow as the recommended way to get started, with manual asset creation available as a secondary option.

### Key Entities

- **Asset Template**: A pre-defined blueprint for a common home item. Key attributes: name, category (from the existing AssetCategory set), suggested location, and an optional description explaining what the item is. Belongs to a template group. Read-only to end users.
- **Template Group**: A logical grouping of related asset templates (e.g., "Kitchen", "Bathroom", "HVAC & Climate"). Key attributes: name, display order. Used for organizing and browsing the template library.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A user can add 5 common home assets via templates in under 2 minutes, compared to 5+ minutes when adding each manually.
- **SC-002**: 90% of users can successfully browse, select, and convert at least one template on their first attempt without guidance.
- **SC-003**: All template-derived assets pass the same validation rules as manually created assets, with zero invalid records created.
- **SC-004**: The template library covers at least 30 common home items across at least 6 groups at launch.
- **SC-005**: Users can customize any pre-filled field before conversion, ensuring 100% of converted assets reflect the user's intended values.

## Assumptions

- Templates are system-managed and shipped as seed data. There is no admin interface for managing templates in this version; templates are added via database seeders.
- Template groups are a flat organizational structure (no nested sub-groups).
- The existing asset creation infrastructure (model, validation rules, database) from 001-home-asset-crud is fully in place and reused for template-to-asset conversion.
- Templates do not include date fields (purchase date, install date, warranty expiration) — these are personal to each homeowner and are left blank by default, editable during the customization step.
- The template library is the same for all users. There is no personalization or recommendation engine in this version.
- Users who want to add items not in the template library use the existing manual "Add Asset" flow.
