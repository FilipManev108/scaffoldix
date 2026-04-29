<!-- BEGIN:nextjs-agent-rules -->
# This is NOT the Next.js you know

This version has breaking changes — APIs, conventions, and file structure may all differ from your training data. Read the relevant guide in `node_modules/next/dist/docs/` before writing any code. Heed deprecation notices.
<!-- END:nextjs-agent-rules -->

# ScaffoldIX Frontend Agent Rules

## Project Context

This is the frontend application for ScaffoldIX, a portfolio-grade project management SaaS app.

The frontend uses:

- Next.js
- TypeScript
- App Router
- Tailwind CSS
- `src/` directory
- API communication with a Laravel backend

## Main Rules

- Use TypeScript.
- Place app routes inside `src/app`.
- Place reusable components inside `src/components`.
- Place feature-specific code inside `src/features`.
- Place shared utilities inside `src/lib`.
- Place shared types inside `src/types`.
- Use Next.js App Router file-based routing.
- Define routes through folders and `page.tsx` files inside `src/app`.
- Use `layout.tsx` for shared route layouts.
- Use `loading.tsx`, `error.tsx`, and `not-found.tsx` where appropriate.
- Use `next/link` for navigation links.
- Use `next/navigation` for programmatic navigation.

## Do Not

- Do not use React Router.
- Do not install `react-router-dom`.
- Do not create a `pages/` directory.
- Do not create route configuration objects like in plain React apps.
- Do not create an `app/` folder outside `src/`.
- Do not hardcode backend URLs directly inside components.
- Do not scatter raw `fetch` calls everywhere.
- Do not treat frontend permission checks as real security.
- Do not make large architecture changes without updating documentation.

## API Rules

All API communication should go through shared API helpers in:

```txt
src/lib/