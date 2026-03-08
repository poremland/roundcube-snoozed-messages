#!/bin/bash

# Verification script for INSTALL.md Step 5 updates

KEYWORDS=(
    "crontab"
    "crontab -e"
    "*/1 * * * *"
    "/path/to/php"
    "/path/to/roundcube"
    "curl"
    "wget"
    "Verify"
)

EXIT_CODE=0

for KEYWORD in "${KEYWORDS[@]}"; do
    if grep -q "$KEYWORD" INSTALL.md; then
        echo "PASS: Keyword '$KEYWORD' found."
    else
        echo "FAIL: Keyword '$KEYWORD' NOT found."
        EXIT_CODE=1
    fi
done

exit $EXIT_CODE
