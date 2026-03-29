# FormFlow Lite - Accessibility Standards and Testing

This document outlines FormFlow Lite's commitment to accessibility and the testing infrastructure we've implemented to ensure WCAG 2.1 Level AA compliance.

## Accessibility Commitment

FormFlow Lite is committed to being accessible to all users, including those with disabilities. This is critical for a forms plugin, as form accessibility directly impacts the ability of users with disabilities to complete critical tasks like enrollments, registrations, and submissions.

We follow **WCAG 2.1 Level AA** as our standard, ensuring:
- Perceivable content (visible, audible, and sensible)
- Operable interfaces (keyboard navigable, sufficient time)
- Understandable content and operations
- Robust code compatible with assistive technologies

## Form Accessibility - Critical Priority

As a forms management plugin, form accessibility is our highest priority. The following guidelines apply to all form components and form-building features:

### Form Structure and Organization

#### Labels
- Every form input MUST have an associated `<label>` element
- Labels must be connected via the `htmlFor` attribute matching the input's `id`
- Labels should be visible and descriptive
- Required field indicators (`*`) should be accompanied by `required` attribute

Example:
```jsx
<label htmlFor="email">Email Address</label>
<input id="email" type="email" required />
```

#### Fieldsets and Legends
- Related groups of form inputs should be wrapped in `<fieldset>` elements
- Each fieldset should have a `<legend>` element describing the group
- Legends help organize related fields and announce context to screen readers

Example:
```jsx
<fieldset>
  <legend>Billing Information</legend>
  <Input label="Billing Address" />
  <Input label="City" />
  <Input label="ZIP Code" />
</fieldset>
```

#### Form Validation and Error Messages
- All error messages must be associated with form fields using `aria-describedby`
- Error states should be marked with `aria-invalid="true"`
- Error text color alone should NOT convey information - use icons or text
- Help text and error messages should be accessible to screen readers

Example:
```jsx
<Input
  label="Email"
  aria-describedby="email-error"
  aria-invalid={!!error}
/>
{error && <span id="email-error" role="alert">{error}</span>}
```

#### Required Fields
- Use the HTML `required` attribute on required inputs
- Indicate required fields visually with an asterisk or "Required" text
- Use `aria-required="true"` if applicable
- Avoid hiding required indicators from visual users

#### Input Types
- Use appropriate input types: `email`, `tel`, `number`, `date`, `password`, etc.
- This helps browsers provide correct validation, mobile keyboard selection, and assistive technology support

### Button Accessibility

#### Button Text
- All buttons must have clear, descriptive text
- Icon-only buttons must have an `aria-label` attribute
- Avoid generic text like "Click here" or "Submit"

#### Focus States
- All buttons must have visible focus indicators (handled by CSS with `focus:ring-2`)
- Focus states must be keyboard navigable
- Focus order should be logical and predictable

#### Disabled State
- Use the `disabled` attribute to disable buttons
- Visually indicate disabled state
- Disabled buttons should not be focusable

Example:
```jsx
<Button
  disabled={isLoading}
  aria-label={isLoading ? "Submitting form" : "Submit"}
>
  {isLoading ? "Submitting..." : "Submit"}
</Button>
```

### Keyboard Navigation

#### Tab Order
- Form elements should follow a logical, left-to-right, top-to-bottom tab order
- Use the HTML tab order (order in DOM) rather than CSS positioning
- Do not trap focus within components
- Provide escape keys for modals and overlays

#### Keyboard Support
- All interactive elements must be operable via keyboard
- Buttons, links, and form inputs must work with Enter/Space keys
- Complex widgets (dropdowns, modals) should follow WAI-ARIA patterns

### Color Contrast

All text must meet WCAG AA standards:
- Normal text: minimum 4.5:1 contrast ratio
- Large text (18pt+): minimum 3:1 contrast ratio
- Form states (error, success, disabled) must maintain contrast
- Don't rely on color alone to convey meaning

This is enforced via automated testing with axe-core.

### Headings and Content Structure

#### Heading Hierarchy
- Use `<h1>` for main page title (only one per page)
- Use `<h2>` for major sections
- Use `<h3>` for subsections within sections
- Don't skip heading levels (e.g., `<h1>` → `<h3>`)

#### Semantic HTML
- Use appropriate semantic elements: `<nav>`, `<main>`, `<section>`, `<article>`
- Avoid using divs when semantic elements would be more appropriate
- Structure content with proper nesting

### Navigation Accessibility

#### Link Text
- Links must have descriptive text content
- Avoid generic link text like "Click here", "Learn more" (use context)
- Icon links must have `aria-label` attributes

#### Navigation Structure
- Main navigation should be in a `<nav>` element with `aria-label`
- Provide skip links to bypass navigation
- Use breadcrumbs for deep navigation structures

### ARIA Attributes

#### aria-label
Use for elements without visible text:
```jsx
<button aria-label="Close dialog">×</button>
```

#### aria-labelledby
Link elements to headings or titles:
```jsx
<div role="dialog" aria-labelledby="dialog-title">
  <h2 id="dialog-title">Confirm Delete</h2>
</div>
```

#### aria-describedby
Link form fields to error messages or help text:
```jsx
<Input aria-describedby="password-rules" />
<p id="password-rules">At least 8 characters, one number</p>
```

#### aria-invalid
Indicate invalid form fields:
```jsx
<input aria-invalid={hasError} />
```

#### aria-required
Explicitly mark required fields:
```jsx
<input aria-required="true" required />
```

### Live Regions

#### Status Messages
Use `role="status"` with `aria-live="polite"` for non-urgent status updates:
```jsx
<div role="status" aria-live="polite">
  Form saved successfully
</div>
```

#### Alert Messages
Use `role="alert"` with `aria-live="assertive"` for urgent messages:
```jsx
<div role="alert" aria-live="assertive">
  Please fix the errors below
</div>
```

### Charts and Data Visualization

#### Alt Text for Visual Content
- Provide meaningful descriptions using `aria-label`
- Describe the main takeaway, not every data point

Example:
```jsx
<div
  role="img"
  aria-label="Form submissions increased 40% in Q1, with peak activity in March"
>
  {/* Chart component */}
</div>
```

#### Data Tables as Alternatives
- Provide data in tabular format alongside visualizations
- Include proper `<thead>`, `<tbody>`, and header cells
- Use scope attributes on headers

#### Sufficient Color Contrast
- Charts must maintain 4.5:1 contrast for all text
- Don't rely on color alone to distinguish chart elements
- Provide legends and descriptions

## Testing Infrastructure

### Automated Testing with Axe-Core

We use **jest-axe** and **axe-core** for automated accessibility testing:

```bash
npm run test:a11y
```

This runs our accessibility test suite and automatically checks for:
- Missing alt text and ARIA labels
- Color contrast violations
- Missing form labels and descriptions
- Invalid ARIA usage
- Heading hierarchy issues
- Keyboard accessibility problems

### Running Tests Locally

#### Run All Tests
```bash
npm run test
```

#### Run Accessibility Tests Only
```bash
npm run test:a11y
```

#### Run Tests in Watch Mode
```bash
npm run test:watch
```

#### Run Script
```bash
./scripts/a11y-check.sh          # Run tests once
./scripts/a11y-check.sh --watch  # Run in watch mode
```

### CI Pipeline

The `.github/workflows/accessibility.yml` workflow runs on every pull request:

1. **ESLint with jsx-a11y** - Lints code for accessibility violations
2. **Vitest with axe-core** - Runs comprehensive automated tests
3. **Artifact Upload** - Stores test results for review

All accessibility tests must pass before merging to main.

## Component-Specific Guidelines

### Input Component
- Must have associated label
- Should display required indicator if required
- Must show error messages with proper styling and screen reader announcement
- Should support all input types (email, password, number, date, etc.)
- Must maintain focus visible indicator

### Button Component
- All buttons must have descriptive text or `aria-label`
- Must support disabled state
- Must have visible focus indicator
- Focus ring must have sufficient contrast

### Modal Component
- Must have proper dialog role
- Must be keyboard dismissible (Escape key)
- Must return focus to trigger element on close
- Initial focus should be on first interactive element or close button
- Must not trap focus

### Badge Component
- Should not convey information by color alone
- Should have sufficient contrast ratio
- May use aria-label if context is unclear

### Select/Dropdown
- Should use native `<select>` when possible
- Custom dropdowns must implement WAI-ARIA Listbox pattern
- Must be keyboard navigable (arrow keys, Enter, Escape)

### Checkbox
- Must have associated label
- Should display checked state
- Must be keyboard accessible

### Radio Button Group
- Must use `<fieldset>` and `<legend>`
- Each radio must have associated label
- Must be keyboard navigable (arrow keys)

## Known Limitations and Future Improvements

1. **Custom Date Picker** - If implementing custom date picker, ensure keyboard navigation and proper ARIA roles
2. **Rich Text Editor** - Ensure contenteditable regions have proper ARIA labels and keyboard support
3. **Drag and Drop** - If implemented, provide keyboard alternatives
4. **Custom Select** - Ensure full WAI-ARIA Combobox pattern implementation

## Resources

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [WAI-ARIA Practices](https://www.w3.org/WAI/ARIA/apg/)
- [Testing with axe-core](https://github.com/dequelabs/axe-core)
- [jest-axe Documentation](https://github.com/nickcolley/jest-axe)
- [Web Accessibility by Google](https://www.udacity.com/course/web-accessibility--ud891)
- [Form Design Best Practices](https://www.smashingmagazine.com/2022/09/inline-validation-web-forms-ux/)

## Reporting Accessibility Issues

If you find an accessibility issue in FormFlow Lite:

1. Create a GitHub issue with the `accessibility` label
2. Include:
   - Description of the issue
   - Steps to reproduce
   - Browser and screen reader used (if applicable)
   - Expected behavior vs actual behavior
   - Screenshot or video if helpful

3. Assign to the accessibility team for review

## Glossary

- **WCAG** - Web Content Accessibility Guidelines
- **AA** - Conformance level meeting stricter accessibility standards
- **ARIA** - Accessible Rich Internet Applications (provides semantic meaning to dynamic content)
- **Axe** - Automated accessibility testing engine
- **Jest-axe** - Jest integration for axe testing
- **Screen Reader** - Software that reads page content aloud (NVDA, JAWS, VoiceOver)
- **Keyboard Navigation** - Ability to navigate and interact using only a keyboard

## Contact

For accessibility questions or concerns, contact the FormFlow Lite team. Accessibility is a priority and we take all feedback seriously.
