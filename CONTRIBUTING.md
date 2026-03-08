# Contributing to Roundcube Snoozed Messages Plugin

Thank you for your interest in contributing to the Roundcube Snoozed Messages plugin! We welcome contributions from the community to help make this plugin better.

## How to Contribute

### 1. Reporting Bugs

If you find a bug, please check the [Issue Tracker](https://github.com/poremland/roundcube-snoozed-messages/issues) to see if it has already been reported. If not, please open a new issue and include:

- A clear and descriptive title.
- Steps to reproduce the bug.
- Your Roundcube and PHP versions.
- Any relevant error messages from the Roundcube logs.

### 2. Suggesting Enhancements

We welcome suggestions for new features and improvements. Please open an issue and describe:

- The goal of the enhancement.
- How it should work.
- Why it would be useful for other users.

### 3. Pull Requests

We love pull requests! To ensure a smooth process, please follow these guidelines:

1. **Fork the repository** and create a new branch for your changes.
2. **Follow the code style** used in the project.
3. **Write tests** for any new functionality or bug fixes.
4. **Ensure all tests pass** before submitting your pull request.
5. **Describe your changes** in detail in the pull request description.

## Code Style

This project follows standard PHP and JavaScript coding conventions. Please ensure your code is clean, readable, and well-documented.

## Testing

We use PHPUnit for backend tests. Please run the test suite before submitting any changes:

```bash
vendor/bin/phpunit --bootstrap tests/bootstrap.php tests
```

## License

By contributing to this project, you agree that your contributions will be licensed under the project's [AGPL v3 License](LICENSE).
