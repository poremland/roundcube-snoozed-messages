# Implementation Plan: Open Source Preparation (AGPL v3)

## Phase 1: Licensing & Headers [checkpoint: fce876d]
- [x] Task: Create `LICENSE` file with full AGPL v3 text [b32b04b]
- [x] Task: Add AGPL-3.0-or-later headers to all PHP files [e8a7b49]
- [x] Task: Add AGPL-3.0-or-later headers to all JS files [4ddf2a6]
- [x] Task: Update `composer.json` with AGPL-3.0-or-later license and author metadata [6073b81]
- [x] Task: Conductor - User Manual Verification 'Phase 1: Licensing & Headers' (Protocol in workflow.md) [fce876d]

## Phase 2: Documentation & Metadata [checkpoint: 73941a0]
- [x] Task: Update `README.md` with overview, features, and AGPL v3 section [c577a17]
- [x] Task: Create `INSTALL.md` with detailed installation and configuration steps [acc8784]
- [x] Task: Create `CONTRIBUTING.md` with guidelines for contributors [8174b7c]
- [x] Task: Create `CHANGELOG.md` with initial release history [ce02d26]
- [x] Task: Verify `composer.json` type is `roundcube-plugin` and required metadata is complete [0212662]
- [x] Task: Conductor - User Manual Verification 'Phase 2: Documentation & Metadata' (Protocol in workflow.md) [73941a0]

## Phase 3: Scrubbing & Style Audit [checkpoint: 27d4a20]
- [x] Task: Scan for and remove hardcoded paths, test credentials, or internal URLs [ff318f0]
- [x] Task: Audit `rcube::write_log` calls and ensure they are appropriate for production [n/a]
- [x] Task: Run a final code style audit across the project (PHP, JS, CSS) [ffa8a84]
- [x] Task: Conductor - User Manual Verification 'Phase 3: Scrubbing & Style Audit' (Protocol in workflow.md) [27d4a20]

## Phase 4: GitHub Community & CI/CD [checkpoint: b3ecccb]
- [x] Task: Create `.github/ISSUE_TEMPLATE/bug_report.md` [8b5d5bd]
- [x] Task: Create `.github/ISSUE_TEMPLATE/feature_request.md` [9b9f37f]
- [x] Task: Implement `.github/workflows/tests.yml` to run `vendor/bin/phpunit` on push/PR [2883edd]
- [x] Task: Verify GitHub Actions successfully execute existing tests [n/a]
- [x] Task: Conductor - User Manual Verification 'Phase 4: GitHub Community & CI/CD' (Protocol in workflow.md) [b3ecccb]

## Phase: Review Fixes
- [x] Task: Apply review suggestions [1c851e7]
