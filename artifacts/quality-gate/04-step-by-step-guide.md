# Laravel Quality Gate Guide

## 1) Install dependencies

```bash
composer install
npm install
```

## 2) Install quality tools

```bash
composer require --dev larastan/larastan:^3.1 phpstan/phpstan:^2.1 squizlabs/php_codesniffer:^3.11
npm install --save-dev husky@^9.1.7 lint-staged@^15.5.2
```

## 3) Activate Git hooks

```bash
npm run prepare
chmod +x .husky/pre-commit
chmod +x .husky/pre-push
```

## 4) Manual checks (local)

```bash
composer pint:check
composer phpcs
composer phpstan
composer test
```

## 5) All-in-one quality command

```bash
composer quality
```

## 6) Commit flow

1. Stage changed files.
2. Run commit as usual.
3. Husky runs lint-staged automatically using fast file-level checks (Pint + PHPCS) only for staged PHP files.
4. Husky runs `composer phpstan` automatically on push through pre-push hook.

## Troubleshooting

- If hook does not run, execute `npm run prepare` and verify `.husky/pre-commit` and `.husky/pre-push` executable permissions.
- If PHPStan runs out of memory, increase `--memory-limit` in composer script.
- If MongoDB is unavailable locally, run tests in Docker or rely on GitHub Actions tests job.
