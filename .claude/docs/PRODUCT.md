# Product

## Register

brand

## Users

Car owners in Russia looking for a trustworthy auto-service (СТО). They arrive on the public site to understand what the shop does, book a repair/maintenance appointment, and later check the status and history of their visits via an email-OTP lookup (no account required). Context: often on a phone, sometimes stressed (their car needs work), varying technical literacy. The job to be done: "find this shop credible, book a slot without friction, and check on my car."

A separate staff-facing Filament admin panel exists at `/admin` (product register) but is out of scope for brand work; this PRODUCT.md governs the **public site**.

## Product Purpose

The public site is the storefront for an auto-service management system (АИС «Автосервис»). It exists to convert visitors into booked appointments and to give existing clients a self-service window into their visits. Success looks like: a visitor trusts the shop within seconds, completes the booking wizard without dropping off, and returns to the lookup page to track work, all on mobile.

## Brand Personality

Industrial, premium, confident, technical. The voice is precise and competent, like a master mechanic who knows exactly what your car needs, not a salesperson. Polished steel and amber/gold over a deep dark base: the feel of a high-end workshop, not a budget garage. Cinematic but controlled (the engine-warmup loader, scroll reveals, metallic surfaces). Emotional goal: reassurance through evident expertise.

## Anti-references

- **Cheap auto-shop sites**: loud red/orange clip-art, garish gradients, clutter, stock photos of mechanics giving a thumbs-up. The opposite of the credibility we want.
- **Toy / playful**: rounded bubbly cartoon styling, pastel palettes, bouncy/elastic animation. Undermines the technical-expert positioning.
- **Generic SaaS template energy**: identical icon-card grids, hero-metric blocks, a tracked uppercase eyebrow above every section.

## Design Principles

1. **Expertise is the proof.** Show competence through precision and finish (tight alignment, real specifics, tabular numbers), not through claims or buzzwords.
2. **Confidence through restraint.** The cinematic touches (glow, shine, reveals) earn their place by being deliberate and sparse; one strong moment beats ten effects competing.
3. **Booking is the spine.** Every public surface points toward booking or visit-lookup without friction; remove anything that doesn't serve trust or conversion.
4. **Mobile is the primary stage.** Most car owners arrive on a phone, often stressed; the phone layout is the real design, desktop is the embellishment.
5. **Material honesty.** The metaphor is real machined metal (steel, chrome, amber heat), used consistently, never as gimmick.

## Accessibility & Inclusion

Target WCAG 2.1 AA. Body text ≥4.5:1 against its (dark) background, large text ≥3:1; watch muted ink shades on the near-black base. Visible `:focus-visible` states (already amber-outlined in the public scope). Every animation needs a `prefers-reduced-motion: reduce` alternative (the reveal/loader system already has one; keep that invariant). Touch targets comfortable on phones.
