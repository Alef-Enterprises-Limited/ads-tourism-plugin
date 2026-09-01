# Accessibility and builder compatibility

## Automated checks

- Pagination has an accessible navigation label and identifies the current page.
- AJAX result regions expose polite status announcements and busy state.
- Listing grids use list/listitem semantics and retain keyboard-focus targets after updates.
- Map output has an accessible label, status text, and non-map directions fallback.
- Empty and error states use status or alert semantics.
- Divi compatibility tests confirm all five post types are added without removing existing types.

## Manual release checks

Test at keyboard-only navigation and 200% browser zoom with a current default WordPress theme and the supported Divi version:

- search, filter, sort, numbered pagination, and load-more controls have visible focus;
- labels remain associated with inputs and controls keep a logical tab order;
- results are announced once after AJAX replacement and focus is not trapped;
- cards, galleries, captions, and commerce controls have meaningful link/button text;
- content remains readable at 320 CSS pixels without horizontal page scrolling;
- reduced-motion preference does not hide information;
- default single/archive/taxonomy templates work without Divi;
- Divi Theme Builder can target each tourism post type and override one specific record;
- two shortcode contexts on one page do not exchange filters or pagination state.

Record theme and Divi versions plus screenshots for any failure or intentional visual change.
