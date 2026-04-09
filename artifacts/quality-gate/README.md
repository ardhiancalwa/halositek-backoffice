# Quality Gate Artifacts

## Files in this folder

- `01-setup-quality-gate.sh`: one-shot Bash setup script.
- `04-step-by-step-guide.md`: detailed implementation and verification guide.
- `config/pint.json`: Pint formatter config.
- `config/phpstan.neon`: PHPStan static analysis config.
- `config/.phpcs.xml`: PHPCS PSR-12 coding standard config.
- `config/phpdoc.xml`: optional PHPDoc generator config template.
- `config/quality.workflow.yml`: GitHub Actions workflow.
- `config/composer.updates.json`: required composer additions.
- `config/package.updates.json`: required package.json additions.
- `phpdoc-examples/`: PHPDoc reference for Model, Controller, Helper, and Service.
- `.husky/pre-push`: run `composer phpstan` automatically before push.

## Quick usage

```bash
bash artifacts/quality-gate/01-setup-quality-gate.sh
```

After setup, run:

```bash
composer quality
```
