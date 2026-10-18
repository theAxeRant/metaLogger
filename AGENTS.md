# Repository Guidelines

## Project Structure & Module Organization

metaLogger is a PHP CLI application that reports server metrics to a remote logging service.

- `symfony/src/`: application code under `Theaxerant\Metalogger\`; `Command/` contains CLI commands, `Parser/` collects metrics, `Entity/` holds location models, and `Util/` provides validation and helpers.
- `symfony/src/resources/default.yaml`: configuration template.
- `symfony/console`: CLI entry point.
- `symfony/test/`: PHPUnit tests mirroring source directories.
- `devops/`: development Dockerfile and Compose configuration.
- `release/metaLogger.phar`: packaged executable; build settings live in `symfony/box.json`.

## Build, Test, and Development Commands

First copy `devops/dev/compose.override.yaml.example` to `devops/dev/compose.override.yaml` and replace `<path to solution>` with this checkout's absolute path.

- `make build`: build the PHP development image.
- `make up`: start development containers.
- `make bash`: start containers and open a PHP container shell.
- `make phar`: build the image, start containers, install Composer dependencies, package the PHAR into `release/`, and stop containers.
- Inside the container, `composer install` installs dependencies from `symfony/composer.lock`.
- From `symfony/`, `php console list` lists commands; `php console config:validate /path/to/config.yaml` checks a configuration.

## Coding Style & Naming Conventions

Use four spaces for PHP indentation, PascalCase class names matching filenames, camelCase methods, and uppercase constants. Follow surrounding brace style; existing files vary. Preserve compatibility with the PHP 7.2 development environment. Add types and PHPDoc where useful. New metric parsers should implement `ParserInterface`. No formatter or linter is configured.

## Testing Guidelines

PHPUnit `^8.5` is the testing framework. After installing dependencies, run from `symfony/`:

```sh
vendor/bin/phpunit --bootstrap vendor/autoload.php test/
```

The actual directory is `test/`; Composer's development autoload mapping currently points to `tests/`. Name files `*Test.php`, extend `TestCase`, and use data providers for input variations. Add regression tests for changed behavior. No coverage threshold is configured.

## Commit & Pull Request Guidelines

History uses short descriptive subjects, such as `External ip check`; no mandatory prefix convention is evident. Keep commits focused. Follow `.github/PULL_REQUEST_TEMPLATE.md`: describe motivation, link fixed issues, identify dependencies and change type, and provide reproducible test commands and configuration details. Complete its self-review and documentation checklist.

## Security & Configuration

Keep real `auth_key` values and machine-specific configurations out of commits. Use the default YAML as a template and absolute paths for cron jobs.
