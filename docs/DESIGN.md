---
name: Civic Utility System
colors:
  surface: '#f9f9f9'
  surface-dim: '#dadada'
  surface-bright: '#f9f9f9'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f3f4'
  surface-container: '#eeeeee'
  surface-container-high: '#e8e8e8'
  surface-container-highest: '#e2e2e2'
  on-surface: '#1a1c1c'
  on-surface-variant: '#3e4a3d'
  inverse-surface: '#2f3131'
  inverse-on-surface: '#f0f1f1'
  outline: '#6e7b6c'
  outline-variant: '#bdcaba'
  surface-tint: '#006e2d'
  primary: '#006b2c'
  on-primary: '#ffffff'
  primary-container: '#00873a'
  on-primary-container: '#f7fff2'
  inverse-primary: '#62df7d'
  secondary: '#735c00'
  on-secondary: '#ffffff'
  secondary-container: '#fed01b'
  on-secondary-container: '#6f5900'
  tertiary: '#a72d51'
  on-tertiary: '#ffffff'
  tertiary-container: '#c74668'
  on-tertiary-container: '#fffbff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#7ffc97'
  primary-fixed-dim: '#62df7d'
  on-primary-fixed: '#002109'
  on-primary-fixed-variant: '#005320'
  secondary-fixed: '#ffe083'
  secondary-fixed-dim: '#eec200'
  on-secondary-fixed: '#231b00'
  on-secondary-fixed-variant: '#574500'
  tertiary-fixed: '#ffd9de'
  tertiary-fixed-dim: '#ffb2bf'
  on-tertiary-fixed: '#3f0016'
  on-tertiary-fixed-variant: '#8a143c'
  background: '#f9f9f9'
  on-background: '#1a1c1c'
  surface-variant: '#e2e2e2'
  surface-background: '#f8fafc'
  text-main: '#0f172a'
  text-muted: '#475569'
  border-subtle: '#e2e8f0'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.05em
  data-mono:
    fontFamily: monospace
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 32px
  sidebar-width: 260px
  max-width: 1440px
---

## Brand & Style

This design system is engineered for municipal efficiency, balancing the authority of government infrastructure with modern digital accessibility. The brand personality is dependable, transparent, and highly functional, catering to public sector administrators and citizens who require immediate, unambiguous information.

The visual style is **Corporate / Modern** with a strict adherence to **Minimalism**. It utilizes expansive white space and high-contrast color pairings to ensure readability across various environments, from high-glare outdoor kiosks to desktop workstations. By stripping away decorative elements, the system highlights critical data and navigational paths, fostering a sense of institutional trust and operational clarity.

## Colors

The palette is anchored by a "Municipal Green" to evoke safety, growth, and public service, paired with a "Caution Yellow" for secondary actions and high-visibility alerts.

- **Primary (#16a34a):** Used for primary call-to-action buttons, success states, and brand-identifying headers.
- **Secondary (#facc15):** Utilized for warning indicators, highlights, and secondary interactive elements. This color is always paired with dark text to meet WCAG AA accessibility standards.
- **Neutral / Surfaces:** The system primarily uses pure white (#ffffff) for content containers to maximize contrast. A very light slate gray (#f8fafc) is used for background regions to define boundaries without introducing visual noise.
- **Typography & UI:** Deep charcoal (#0f172a) is used for maximum legibility on white surfaces, ensuring the interface is accessible to users with visual impairments.

## Typography

The typography system relies exclusively on **Inter** for its neutral, systematic appearance and high legibility in technical contexts.

- **Data Density:** Use `data-mono` for all numeric strings, IDs, and timestamps to ensure vertical alignment in tables.
- **Hierarchy:** Headlines use tighter tracking and heavier weights to create a clear visual anchor. 
- **Accessibility:** Ensure that body text never falls below 14px. Labels use increased letter-spacing when rendered in uppercase for improved scannability in navigation and table headers.

## Layout & Spacing

This design system utilizes a **Fixed Grid** model to maintain a structured, institutional feel.

- **Desktop:** A 12-column grid with a 1440px max-width. Content is typically offset by a persistent sidebar.
- **Tablet:** An 8-column grid where the sidebar transitions to a collapsed rail or hidden drawer.
- **Mobile:** A single-column flow with 16px horizontal margins.

Spacing follows a strict 8px (0.5rem) linear scale. Internal component padding should be generous (typically 16px or 24px) to prevent information from appearing cluttered, which is essential for administrative workflows.

## Elevation & Depth

To maintain high contrast and a clean, municipal aesthetic, elevation is conveyed through **Low-contrast outlines** and **Tonal layers**. Shadows are used sparingly as a functional cue rather than a decorative effect.

- **Level 0:** Main page background (#f8fafc).
- **Level 1:** Primary cards and containers. Pure white background with a 1px solid border (#e2e8f0).
- **Level 2:** Active or focused elements. Adds a subtle ambient shadow (4px blur, 10% opacity) to signify the item is lifted.
- **Modals:** High-contrast elevation with a dark scrim (40% opacity) and a defined 12px blur shadow to separate the dialog from the background utility.

## Shapes

The shape language is **Rounded**, utilizing an 8px (0.5rem) base radius for standard components. This softens the "institutional" feel, making the software feel modern and accessible.

- **Base (0.5rem):** Buttons, input fields, and standard cards.
- **Large (1rem):** Main content areas and modal containers.
- **Pill:** Reserved for status badges and tags (e.g., "Active", "Pending") to differentiate them from interactive button elements.

## Components

### Buttons
- **Primary:** Municipal Green (#16a34a) with White text. Bold weight.
- **Secondary:** White background with a 1px Municipal Green border and Green text.
- **Warning:** Secondary Yellow (#facc15) with Dark Slate (#0f172a) text.

### Input Fields
Inputs use white backgrounds, 1px #e2e8f0 borders, and 8px horizontal padding. The focus state is indicated by a 2px Primary Green ring. Labels should always be visible above the field for accessibility.

### Cards
Cards are the primary container for data. They must feature a white background, 8px corner radius, and a 1px border. Avoid nesting cards more than one level deep.

### Data Tables
Tables are high-density. Header rows use a light slate background and `label-sm` typography. Alternating row stripes (Zebra striping) are encouraged in large datasets to assist eye tracking.

### Status Chips
Status chips use the pill shape. Use semantic colors: Green for "Healthy/Active", Yellow for "Review Required", and Red for "Critical/Error". Ensure the text-to-background contrast ratio is at least 4.5:1.