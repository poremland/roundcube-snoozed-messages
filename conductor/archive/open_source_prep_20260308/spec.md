# Specification: Open Source Preparation (AGPL v3)

## Overview
Prepare the Roundcube Snooze Plugin for public release. This includes ensuring proper licensing (AGPL v3), creating necessary documentation for GitHub and the Roundcube plugin repository, scrubbing any internal data, and establishing a baseline for community contribution.

## Scope
- **Licensing:** AGPL v3 application across the codebase.
- **Documentation:** README, LICENSE, INSTALL, CONTRIBUTING, and CHANGELOG.
- **Project Metadata:** `composer.json` update for Roundcube plugin repository compatibility.
- **Cleanup:** Removal of internal data, debug logs, and a final code style audit.
- **CI/CD:** Basic GitHub Actions for automated testing.

## Functional Requirements
- **License Headers:** 
    - Every PHP and JavaScript file must contain the standard AGPL-3.0-or-later header.
- **Documentation:**
    - `README.md`: Overview, features, and license information.
    - `LICENSE`: The full text of the GNU Affero General Public License v3.
    - `INSTALL.md`: Detailed installation and configuration instructions.
    - `CONTRIBUTING.md`: Guidelines for external contributors.
    - `CHANGELOG.md`: Record of all notable changes to the project.
- **Metadata:**
    - Ensure `composer.json` includes `name`, `type: "roundcube-plugin"`, `license: "AGPL-3.0-or-later"`, and `authors`.
- **Scrubbing:**
    - Scan for and remove any hardcoded paths, test credentials, internal URLs, or developer-only comments.
    - Review all `rcube::write_log` calls to ensure they are appropriate for production or removed.
- **GitHub Community:**
    - Create `.github/ISSUE_TEMPLATE/` for bug reports and feature requests.
    - Implement a basic GitHub Action to run PHPUnit tests on push/pull request.

## Non-Functional Requirements
- **Compliance:** Must meet all requirements for submission to the official Roundcube plugin repository.
- **Consistency:** Documentation must follow the "Professional & Clear" tone established in the Product Guidelines.
- **Maintainability:** CI/CD should provide a reliable signal for future contributions.

## Acceptance Criteria
- [ ] All code files contain the AGPL v3 header.
- [ ] `LICENSE` and `composer.json` correctly specify AGPL-3.0-or-later.
- [ ] Documentation (README, INSTALL, CONTRIBUTING, CHANGELOG) is complete and accurate.
- [ ] Automated scan or manual review confirms no internal data or excessive debug logging exists.
- [ ] GitHub Actions successfully run the existing test suite (`vendor/bin/phpunit`) on the repository.
- [ ] The plugin package is valid according to Roundcube plugin repository standards (manual verification).

## Out of Scope
- Implementing new features or fixing existing bugs not related to release readiness.
- Setting up a dedicated website or marketing materials.
- Registering the plugin in the Roundcube repository (this is the *preparation* phase).
