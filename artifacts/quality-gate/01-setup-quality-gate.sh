#!/usr/bin/env bash
set -euo pipefail

# Run this script from project root.
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

echo "[1/7] Install PHP quality tools"
composer require --dev \
  larastan/larastan:^3.1 \
  phpstan/phpstan:^2.1 \
  squizlabs/php_codesniffer:^3.11

echo "[2/7] Install Node git-hook tooling"
npm install --save-dev husky@^9.1.7 lint-staged@^15.5.2

echo "[3/7] Activate husky"
npm run prepare

echo "[4/7] Ensure Git hooks exist"
mkdir -p .husky
cat > .husky/pre-commit <<'HOOK'
#!/usr/bin/env sh

npx lint-staged
HOOK
cat > .husky/pre-push <<'HOOK'
#!/usr/bin/env sh

composer phpstan
HOOK
chmod +x .husky/pre-commit
chmod +x .husky/pre-push

echo "[5/7] Validate composer JSON"
composer validate --no-check-publish

echo "[6/7] Run local quality checks"
composer pint:check
composer phpcs
composer phpstan

echo "[7/7] Run tests"
composer test

echo "Quality gate setup complete."
