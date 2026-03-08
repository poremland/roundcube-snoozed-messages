# Product Guidelines: Roundcube Snooze Plugin

## Design Philosophy
- **Minimalist Integration:** The plugin should feel like a native part of the Roundcube interface. Avoid introducing new or jarring visual elements.
- **Predictability:** Ensure that every snooze action has a clear and expected outcome. Users should always know where their emails are going and when they will return.
- **User-Centric Feedback:** Provide immediate and clear visual feedback for all snooze operations (e.g., successful move to the "Snoozed" folder, setting a custom time).

## Tone and Voice
- **Professional & Clear:** Use concise, professional language for all UI labels, tooltips, and confirmation messages.
- **Helpful & Instructive:** Where necessary, provide brief instructions or tooltips that guide the user through more complex actions (e.g., the custom time picker).
- **Consistently Supportive:** Messages should be reassuring and helpful, avoiding any technical jargon that might confuse the user.

## Accessibility
- **Screen Reader Compatibility:** All new UI elements (buttons, icons, pickers) must be properly labeled for screen readers.
- **Keyboard Navigation:** Ensure that all snooze actions and the custom time picker are fully accessible via keyboard shortcuts and tab navigation.
- **Contrast & Visibility:** Use high-contrast color schemes for all text and icons, following Roundcube's established accessibility standards.

## UX Principles
- **One-Click Snoozing:** Prioritize common, quick-snooze options to minimize the number of clicks required for the most frequent actions.
- **Contextual Awareness:** The "Snooze" button should only be active when one or more emails are selected and can be moved.
- **Transparent State Management:** Ensure users can easily find snoozed emails by providing clear labeling for the "Snoozed" folder.

## Visual Standards
- **Iconography:** Use standard Roundcube icons or create new ones that are visually consistent with the existing style.
- **Color Palette:** Strictly adhere to the color palette used by the active Roundcube skin to ensure a seamless visual experience.
- **Consistency:** Ensure that all dialogs, buttons, and menus match the layout and styling of the surrounding Roundcube interface.
