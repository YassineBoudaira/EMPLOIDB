# EMPLOIDB Color Customization Guide

## Quick Color Customization

To change the colors of your EMPLOIDB platform, simply modify these 3 main variables in `assets/css/emploidb-design-system.css`:

```css
:root {
    /* === MAIN BRAND COLORS (EASILY CUSTOMIZABLE) === */
    --emploidb-brand-primary: #2563eb;      /* Main Brand Color - Change this to your preferred primary color */
    --emploidb-brand-secondary: #059669;    /* Secondary Brand Color - Change this to your preferred secondary color */
    --emploidb-brand-accent: #f59e0b;       /* Accent Brand Color - Change this to your preferred accent color */
}
```

## Color Examples

### Blue Theme (Current)
```css
--emploidb-brand-primary: #2563eb;    /* Blue */
--emploidb-brand-secondary: #059669;  /* Green */
--emploidb-brand-accent: #f59e0b;     /* Orange */
```

### Purple Theme
```css
--emploidb-brand-primary: #7c3aed;    /* Purple */
--emploidb-brand-secondary: #059669;  /* Green */
--emploidb-brand-accent: #f59e0b;     /* Orange */
```

### Red Theme
```css
--emploidb-brand-primary: #dc2626;    /* Red */
--emploidb-brand-secondary: #059669;  /* Green */
--emploidb-brand-accent: #f59e0b;     /* Orange */
```

### Teal Theme
```css
--emploidb-brand-primary: #0d9488;    /* Teal */
--emploidb-brand-secondary: #059669;  /* Green */
--emploidb-brand-accent: #f59e0b;     /* Orange */
```

### Dark Theme
```css
--emploidb-brand-primary: #1f2937;    /* Dark Gray */
--emploidb-brand-secondary: #374151;  /* Medium Gray */
--emploidb-brand-accent: #f59e0b;     /* Orange */
```

## Standardized CSS Classes

All admin pages now use consistent class names with the `emploidb-` prefix:

### Layout Classes
- `.emploidb-admin-layout` - Main admin layout container
- `.emploidb-admin-sidebar` - Sidebar navigation
- `.emploidb-admin-content` - Main content area

### Component Classes
- `.emploidb-content-card` - Content card containers
- `.emploidb-page-header` - Page header sections
- `.emploidb-stats-grid` - Statistics grid layout
- `.emploidb-stat-card` - Individual statistic cards

### Button Classes
- `.emploidb-action-btn` - Base action button
- `.emploidb-action-btn.emploidb-primary` - Primary button
- `.emploidb-action-btn.emploidb-success` - Success button
- `.emploidb-action-btn.emploidb-warning` - Warning button
- `.emploidb-action-btn.emploidb-danger` - Danger button
- `.emploidb-action-btn.emploidb-info` - Info button

### Status Badge Classes
- `.emploidb-status-badge` - Base status badge
- `.emploidb-status-badge.emploidb-active` - Active status
- `.emploidb-status-badge.emploidb-suspended` - Suspended status
- `.emploidb-status-badge.emploidb-pending` - Pending status
- `.emploidb-status-badge.emploidb-verified` - Verified status

### Navigation Classes
- `.emploidb-nav-link` - Navigation links
- `.emploidb-nav-link.emploidb-active` - Active navigation item
- `.emploidb-nav-section-header` - Navigation section headers

### Data Display Classes
- `.emploidb-data-card` - Data display cards
- `.emploidb-data-title` - Data card titles
- `.emploidb-data-subtitle` - Data card subtitles
- `.emploidb-data-meta` - Data metadata sections

### Typography Classes
- `.emploidb-page-title` - Main page titles
- `.emploidb-page-subtitle` - Page subtitles
- `.emploidb-stat-number` - Statistic numbers
- `.emploidb-stat-label` - Statistic labels

### Information Display
- `.emploidb-info-grid` - Information grid layout
- `.emploidb-info-item` - Individual info items
- `.emploidb-info-label` - Info labels
- `.emploidb-info-value` - Info values

## How to Apply Changes

1. Open `assets/css/emploidb-design-system.css`
2. Find the `:root` section at the top
3. Change the 3 brand color values
4. Save the file
5. Refresh your browser - all pages will automatically use the new colors!

## Advanced Customization

For more advanced customization, you can also modify:

- Font families: `--emploidb-font-primary`, `--emploidb-font-secondary`
- Spacing: `--emploidb-spacing-*` variables
- Border radius: `--emploidb-radius-*` variables
- Shadows: `--emploidb-shadow-*` variables
- Transitions: `--emploidb-transition-*` variables

All these variables are defined in the design system file and used consistently across all pages.
