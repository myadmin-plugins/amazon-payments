# MyAdmin Amazon Payments Plugin

Composer package (type: `myadmin-plugin`) integrating Amazon Pay into MyAdmin's billing system via Symfony EventDispatcher hooks. CI/CD workflows in `.github/` automate testing and deployment; IDE configuration (inspectionProfiles, deployment.xml, encodings.xml) lives in `.idea/`.

## Commands

```bash
composer install
vendor/bin/phpunit tests/ -v
vendor/bin/phpunit tests/ -v --coverage-clover coverage.xml --whitelist src/
```

```bash
php -l src/amazon.php
php -l src/Plugin.php
```

```bash
composer dump-autoload
composer validate
```

## Architecture

- **Namespace**: `Detain\MyAdminAmazon\` → `src/` · Tests: `Detain\MyAdminAmazon\Tests\` → `tests/`
- **Plugin entry**: `src/Plugin.php` — static class with `$name`, `$description`, `$help`, `$type = 'plugin'`
- **Hooks**: `getHooks()` returns `['system.settings' => [__CLASS__, 'getSettings'], 'function.requirements' => [__CLASS__, 'getRequirements']]`
- **Functions**: `src/amazon.php` — `amazon_obtain_profile()`, `amazon_wallet_widget()`, `amazon_addressbook_widget()`
- **Tests**: `tests/PluginTest.php` · `tests/AmazonFunctionsTest.php` · `tests/FileStructureTest.php`
- **Bootstrap**: `tests/bootstrap.php` — defines `_()` gettext stub
- **Constants**: `AMAZON_CHECKOUT_ENABLED` · `AMAZON_SANDBOX` · `AMAZON_CLIENT_ID` · `AMAZON_SELLER_ID`

## Plugin Conventions

- All hook methods in `src/Plugin.php` are `public static function methodName(GenericEvent $event)`
- Requirements registered via `$loader->add_requirement('function_name', '/../vendor/detain/myadmin-amazon-payments/src/amazon.php')`
- Settings use `$settings->add_radio_setting()`, `add_dropdown_setting()`, `add_text_setting()`, `add_password_setting()`
- Widget functions in `src/amazon.php` return raw HTML strings with inline `<script>` using `OffAmazonPayments.Widgets.*`
- OAuth flow uses `curl_init` → `curl_setopt(CURLOPT_RETURNTRANSFER)` → `curl_exec` → `json_decode`
- i18n: wrap user-facing strings in `_('string')` for gettext
- `composer.json` type must stay `myadmin-plugin`; require `symfony/event-dispatcher *@stable`

## Testing Conventions

- Tests extend `PHPUnit\Framework\TestCase`; use `setUpBeforeClass()` to load source files once
- Content tests assert via `assertStringContainsString()` against `file_get_contents($sourceFile)`
- Structural tests use `ReflectionClass` to verify static properties and method signatures
- `tests/bootstrap.php` must define `_()` stub before autoload for gettext calls in source

## Plugin contract harness

This package is on the shared contract harness from `detain/myadmin-plugin-installer`.
`tests/ContractTest.php` is **generated** — run `composer myadmin:scaffold-tests` (add
`--force --write` to re-emit it), never hand-edit it.

The harness **executes** the plugin: it defines the bare constants the class body references
and then calls `getHooks()`, `getSettings()`, `getMenu()`, `apiRegister()` and — for
`type=service` packages — the activate/deactivate/change-ip/queue handlers, for real.

**So do not write reflection-only tests for the plugin class.** Asserting a handler exists,
is public, is static and takes one parameter passes whether or not the handler works; three
production bugs in this fleet were sitting behind assertions of exactly that shape. Older
guidance in this repo that says those methods must not be called predates the harness.

The harness is **additive**: it runs alongside this package's existing tests, and nothing is
deleted to make room for it. Run the whole suite, never `--filter ContractTest` alone — the
contract class primes constants and calls `register_module()`, neither of which can be undone.

See the `plugin-contract-tests` skill for the full workflow, and `docs/testing-harness.md` in
the installer.

<!-- caliber:managed:pre-commit -->
## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->

<!-- caliber:managed:model-config -->
## Model Configuration

Recommended default: `claude-sonnet-4-6` with high effort (stronger reasoning; higher cost and latency than smaller models).
Smaller/faster models trade quality for speed and cost — pick what fits the task.
Pin your choice (`/model` in Claude Code, or `CALIBER_MODEL` when using Caliber with an API provider) so upstream default changes do not silently change behavior.

<!-- /caliber:managed:model-config -->

<!-- caliber:managed:sync -->
## Context Sync

This project uses [Caliber](https://github.com/caliber-ai-org/ai-setup) to keep AI agent configs in sync across Claude Code, Cursor, Copilot, and Codex.
Configs update automatically before each commit via `/home/my/.nvm/versions/node/v24.15.0/bin/caliber refresh`.
If the pre-commit hook is not set up, run `/setup-caliber` to configure everything automatically.
<!-- /caliber:managed:sync -->
