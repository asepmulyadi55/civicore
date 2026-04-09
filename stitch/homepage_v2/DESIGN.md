# Design System Strategy: The Digital Curator

## 1. Overview & Creative North Star
This design system moves away from the traditional, rigid community portal toward an "Editorial Tech" experience. Our Creative North Star is **The Digital Curator**. Unlike standard platforms that simply list data, this system treats community events as curated gallery pieces.

To achieve a "tech-forward" and "minimalist" aesthetic, we utilize **intentional asymmetry** and **tonal depth**. We break the template look by using oversized typography as structural anchors, allowing imagery to breathe within generous white space. Elements are not just "placed" on a grid; they are layered to suggest a multi-dimensional digital canvas, moving the Dwipapuri brand from "local bulletin" to "premium digital experience."

---

## 2. Colors & Surface Philosophy
The palette centers on a high-contrast relationship between deep, trustworthy navies and vibrant, kinetic violets.

### The "No-Line" Rule
To maintain a high-end minimalist feel, **1px solid borders are strictly prohibited for sectioning.** Boundaries must be defined through background shifts. For example, a content section using `surface-container-low` should sit directly against a `background` or `surface` area. The transition of color is the divider.

### Surface Hierarchy & Nesting
Treat the UI as a series of physical layers. Use the Material tiers to create nested depth:
*   **Base:** `surface` (#f8f9fa) for the primary page background.
*   **Secondary Zones:** `surface-container-low` (#f3f4f5) for large content blocks.
*   **Interactive Cards:** `surface-container-lowest` (#ffffff) to create a subtle lift.
*   **High-Impact Elements:** `primary-container` (#1a237e) for anchoring specific "Live" or "Featured" content.

### The "Glass & Gradient" Rule
For "tech-forward" flair, use Glassmorphism on floating navigation or overlay cards. Apply semi-transparent versions of `surface` with a 12px-20px backdrop-blur. 
*   **Signature Textures:** Use subtle linear gradients for primary CTAs, transitioning from `primary` (#000666) to `secondary` (#5f00e3) at a 45-degree angle. This provides a "digital soul" that flat colors lack.

---

## 3. Typography
We use a dual-typeface system to balance authority with modern friendliness.

*   **Display & Headlines (Plus Jakarta Sans):** These are the "Editorial" voice. Use `display-lg` (3.5rem) with tight letter-spacing for hero sections. The geometric nature of Plus Jakarta Sans provides the "Tech" feel.
*   **Body & Labels (Inter):** Inter handles the "Functional" voice. It provides exceptional readability at small scales (`body-sm` at 0.75rem).
*   **Hierarchy as Identity:** Use `headline-lg` for section titles with significant top-padding to emphasize the "minimalist" commitment to white space.

---

## 4. Elevation & Depth
Elevation is conveyed through **Tonal Layering** rather than heavy shadows.

*   **The Layering Principle:** Instead of a shadow, place a `surface-container-lowest` card on a `surface-container-low` background. The slight shift in hex value creates a "natural lift."
*   **Ambient Shadows:** Where floating elements (like modals or dropdowns) require a shadow, use an ultra-diffused style: `box-shadow: 0 10px 40px rgba(25, 28, 29, 0.06)`. The shadow color is a low-opacity tint of `on-surface` (#191c1d).
*   **The "Ghost Border" Fallback:** If a container needs further definition, use a "Ghost Border": the `outline-variant` token at 15% opacity. Never use 100% opaque lines.
*   **Depth through Blur:** Use `backdrop-filter: blur(10px)` on primary navigation bars to let the vibrant imagery of community events bleed through, creating an integrated, premium feel.

---

## 5. Components

### Buttons
*   **Primary:** Gradient fill (`primary` to `secondary`), `md` (0.75rem) roundedness. Text is `label-md` in `on-primary`.
*   **Secondary:** `surface-container-high` background with `on-surface` text. No border.
*   **Tertiary:** Text-only in `secondary`, using a slight underline or arrow icon for directionality.

### Cards & Lists
*   **The "No Divider" Rule:** Forbid the use of horizontal lines in lists. Separate items using `1.5rem` (xl) vertical spacing and subtle background shifts on hover.
*   **Event Cards:** Use `lg` (1rem) border-radius. Images should use a subtle "zoom" transition on hover to feel responsive and high-tech.

### Chips & Badges
*   **Live Indicators:** Use `error` (#ba1a1a) with a soft pulse animation, but keep the container background `error-container` (#ffdad6) for a sophisticated, layered look.
*   **Filter Chips:** Use `secondary-fixed-dim` for inactive states and `secondary` for active states.

### Input Fields
*   **States:** Use `surface-container-highest` for the field background. The `outline` only appears on focus, using the `secondary` (#5f00e3) color to signify a modern interaction.

### Signature Component: The "Feature Glass"
A specific component for featured events: A large image background with a glassmorphic overlay card positioned asymmetrically (e.g., bottom-right) containing the event details.

---

## 6. Do's and Don'ts

### Do
*   **Do** use asymmetrical layouts where text and images overlap slightly to create depth.
*   **Do** prioritize `primary-fixed-dim` for backgrounds of text-heavy modules to reduce eye strain while maintaining a "tech" vibe.
*   **Do** use generous white space—if it feels "too empty," you are likely on the right track for a minimalist aesthetic.

### Don't
*   **Don't** use 1px solid black or dark grey borders. Use background tonal shifts.
*   **Don't** use generic system fonts. Stick strictly to the Inter/Plus Jakarta Sans pairing.
*   **Don't** cram multiple cards into a tight grid. Allow each card "room to breathe" with at least `2rem` of margin.
*   **Don't** use harsh drop shadows. If it's visible at a glance, it's too dark. Aim for "felt, not seen."