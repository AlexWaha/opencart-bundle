# Contributing

Thanks for your interest in improving the **OpenCart Extension Bundle**! Contributions of all kinds are welcome — bug reports, fixes, new features and documentation.

## Ways to help

- ⭐ **Star the repository** — the easiest way to support the project and help others discover it.
- 🐛 **Report bugs** using the [bug report template](https://github.com/AlexWaha/opencart-bundle/issues/new/choose).
- 💡 **Request features** using the feature request template.
- 🔧 **Submit pull requests** for fixes and improvements.

## Reporting a bug

1. Search [existing issues](https://github.com/AlexWaha/opencart-bundle/issues) (open and closed) first.
2. Open a new issue with the bug template and fill in the OpenCart/PHP version and exact reproduction steps.
3. Include error logs and screenshots — but never passwords, tokens or host details.

## Submitting a pull request

1. Fork the repo and create a branch: `fix/short-description` or `feature/short-description`.
2. Keep changes focused — one logical change per PR.
3. Match the existing code style:
   - PHP 7.4+ typed properties and return types.
   - `.twig` templates only (never `.tpl`).
   - English-only code, comments and commit messages.
   - No debug calls (`var_dump`, `print_r`, `dd`) or `$this->log->write()` in committed code.
4. Run `php -l` on changed files and format with Pint.
5. If you change module files, rebuild the module's `dist/*.ocmod.zip`.
6. Open the PR using the template and describe what changed and how you tested it.

## Project layout

Each extension lives in its own top-level folder:

```
<extension>/
  src/upload/      # files copied into the OpenCart root on install
  dist/*.ocmod.zip # ready-to-install package
  docs/{en,ru}.md  # documentation
  img/             # poster / screenshots
```

Shared helper code (AW Core) lives in `Core/`.

## Questions

For quick questions or paid customization, reach out on [Telegram](https://t.me/alexwaha_dev) or via [alexwaha.com](https://alexwaha.com).
