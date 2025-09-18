# Repository Guidelines

## Project Structure & Module Organization
This Laravel 12 + Filament app keeps controllers, models, and policies grouped by feature in `app/`. Configuration defaults reside in `config/`, while migrations, factories, and seeders in `database/` define the catalog schema and demo data. Assets and Blade templates sit under `resources/` (`resources/js`, `resources/css`, `resources/views`); Vite writes compiled output into `public/`. Docker assets in `docker/` and `docker-compose.yml` reproduce the PHP, queue, and database stack used for local parity. Tests are split between `tests/Feature` for HTTP and Filament flows and `tests/Unit` for isolated helpers.

## Build, Test, and Development Commands
Install dependencies with `composer install && npm install`. Prepare storage with `php artisan migrate --seed`; the default SQLite file lives at `database/database.sqlite`. Run `composer dev` for a concurrent dev loop (Laravel server, queue listener, Pail logs, and `npm run dev`). Build production assets with `npm run build`, or start the containerized stack via `docker-compose up --build`. Clear caches after config changes using `php artisan config:clear`.

## Coding Style & Naming Conventions
Follow PSR-12 and four-space indentation for PHP; format patches with `./vendor/bin/pint`. Name classes in StudlyCase (`BookLoanPolicy`), methods in camelCase, and Blade files in kebab-case (`books/index.blade.php`). JavaScript modules originate from `resources/js` and use ES modules; keep Tailwind utility usage consistent with `tailwind.config.js`.

## Testing Guidelines
Run the suite with `php artisan test` (or `./vendor/bin/phpunit` for targeted runs). Mirror application namespaces in test classes and suffix files with `Test.php`, e.g., `tests/Feature/Books/ManageBooksTest.php`. Use model factories or seeders when asserting Filament tables or dashboards so fixtures mirror production data. New behaviour that touches queues, policies, or API endpoints should ship with both feature and unit coverage.

## Commit & Pull Request Guidelines
Commits follow the repository’s imperative, sentence-case style (`Implement complete FilamentPHP admin system...`). Keep each commit focused on one behavioral change and document schema updates in the body. Pull requests should summarize the intent, link issues or TODOs, list setup steps (migrations, seeds, queues), and attach screenshots or GIFs for UI work. Ping maintainers responsible for affected modules and confirm `composer test` and `npm run build` before requesting review.

## Environment & Security Notes
Copy `.env.example` to `.env`, run `php artisan key:generate`, and never commit secrets. Align queue/cache drivers with the target environment when using Docker, and rotate seeded credentials before sharing preview instances.
