# Feature Specification: Wilson Brand Logo for Authentication Pages

**Feature Branch**: `009-wilson-logo`
**Created**: 2026-03-08
**Status**: Draft
**Input**: User description: "Change the login and register to replace the Laravel logo with a new logo for wilson. Design a SVG logo to use."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Wilson Logo on Login Page (Priority: P1)

A user visiting the login page sees the Wilson brand logo prominently displayed above the login form, replacing the generic Laravel starter kit icon. The logo communicates the Wilson application identity and creates a cohesive branded experience.

**Why this priority**: The login page is the first point of contact for all users. Establishing the correct brand identity here is the most critical and visible change.

**Independent Test**: Fully testable by navigating to the login page and verifying the Wilson logo appears, the Laravel icon does not appear, and the application name is displayed correctly.

**Acceptance Scenarios**:

1. **Given** a user navigates to the login page, **When** the page loads, **Then** the Wilson SVG logo is displayed above the login form card.
2. **Given** the login page is loaded, **When** the user inspects the logo area, **Then** the Laravel starter kit icon (geometric cube shape) is no longer visible.
3. **Given** the login page is loaded in dark mode, **When** the logo renders, **Then** the Wilson logo displays correctly with appropriate colors for the dark background.

---

### User Story 2 - Wilson Logo on Registration Page (Priority: P2)

A user visiting the registration page sees the same Wilson brand logo as on the login page, ensuring visual consistency across all authentication screens.

**Why this priority**: Registration is the second most important auth page. Consistent branding across login and register reinforces the Wilson identity.

**Independent Test**: Fully testable by navigating to the registration page and verifying the same Wilson logo appears as on the login page.

**Acceptance Scenarios**:

1. **Given** a user navigates to the registration page, **When** the page loads, **Then** the Wilson SVG logo is displayed above the registration form card.
2. **Given** both login and registration pages are viewed, **When** comparing the logo area, **Then** the same Wilson logo appears on both pages with identical styling.

---

### User Story 3 - Wilson Logo on All Auth Pages (Priority: P3)

All other authentication-related pages (forgot password, reset password, email verification, two-factor challenge) also display the Wilson logo for complete brand consistency across the entire authentication flow.

**Why this priority**: Secondary auth pages are less frequently visited but should still present a consistent branded experience.

**Independent Test**: Testable by visiting each auth page and confirming the Wilson logo is present on all of them.

**Acceptance Scenarios**:

1. **Given** a user visits any authentication page (forgot password, reset password, verify email, 2FA challenge), **When** the page loads, **Then** the Wilson logo appears consistently above the page content.

---

### Edge Cases

- What happens when the logo is rendered on a light background vs. dark background? The SVG must support both color schemes.
- How does the logo appear at different sizes (mobile vs. desktop screen widths)?
- Does the application name "Wilson" appear as accessible text alongside or near the logo for screen readers?

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The authentication pages (login, register, and all other auth pages) MUST display a custom Wilson SVG logo in place of the current Laravel starter kit icon.
- **FR-002**: The Wilson SVG logo MUST be designed as an inline SVG that renders clearly at small sizes (approximately 36x36 pixels).
- **FR-003**: The Wilson SVG logo MUST support both light and dark color schemes, adapting its colors appropriately to remain legible on both backgrounds.
- **FR-004**: The application name displayed alongside or near the logo MUST read "Wilson" rather than "Laravel Starter Kit".
- **FR-005**: The logo change MUST be applied via the shared logo component (`app-logo-icon`) so all auth pages receive the update consistently without per-page changes.
- **FR-006**: The Wilson logo MUST be accessible, with appropriate screen-reader text identifying the application name.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: The Wilson logo is visible on the login page, replacing the previous Laravel icon — verifiable by visual inspection and automated test.
- **SC-002**: The Wilson logo is visible on the registration page — verifiable by visual inspection and automated test.
- **SC-003**: No references to "Laravel Starter Kit" brand identity remain visible on any authentication page.
- **SC-004**: The logo renders correctly in both light and dark modes without distortion or color contrast issues.
- **SC-005**: The logo SVG is self-contained and requires no additional image file downloads (no external PNG/JPG dependencies).

## Assumptions

- The Wilson logo design will be a text-based or abstract SVG mark using the letter "W" or a stylized representation of the name "Wilson" — a clean, modern design appropriate for a home asset management application.
- The logo will use `currentColor` for fill where possible, enabling easy theming through CSS.
- No separate logo image files (PNG, SVG file assets) are needed — the logo will be inline SVG within the existing Blade component.
- The app name text ("Wilson") displayed near the logo (currently "Laravel Starter Kit" in the sidebar brand) is also in scope for this change.
