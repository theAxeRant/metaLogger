#!/usr/bin/env bash
set -euo pipefail

: "${TARGET_BRANCH:?TARGET_BRANCH is required}"
: "${SOURCE_SHA:?SOURCE_SHA is required}"
: "${PHAR_PATH:?PHAR_PATH is required}"
: "${PHAR_VERSION:?PHAR_VERSION is required}"
: "${PHAR_SHA256:?PHAR_SHA256 is required}"

branch_ref="refs/heads/$TARGET_BRANCH"
git check-ref-format "$branch_ref"
if [ "$(git rev-parse HEAD)" != "$SOURCE_SHA" ]; then
    echo 'The checkout does not match the verified source commit.' >&2
    exit 1
fi
if [ -n "$(git status --porcelain)" ]; then
    echo 'PHAR publication requires a clean checkout.' >&2
    exit 1
fi

# Publish only the exact source commit that was verified.
git fetch --no-tags origin "$branch_ref"
if [ "$(git rev-parse FETCH_HEAD)" != "$SOURCE_SHA" ]; then
    echo 'The branch advanced; skipping this stale PHAR.'
    exit 0
fi

actual_sha256=$(sha256sum "$PHAR_PATH")
if [ "${actual_sha256%% *}" != "$PHAR_SHA256" ]; then
    echo 'The downloaded PHAR does not match the verified checksum.' >&2
    exit 1
fi
mkdir -p release
install -m 0755 "$PHAR_PATH" release/metaLogger.phar
git add -- release/metaLogger.phar
if git diff --cached --quiet; then
    echo 'The branch already contains this PHAR.'
    exit 0
fi

git -c user.name='github-actions[bot]' \
    -c user.email='41898282+github-actions[bot]@users.noreply.github.com' \
    commit -m "Build PHAR $PHAR_VERSION from ${SOURCE_SHA:0:12}"

if git push origin "HEAD:$branch_ref"; then
    echo "Published PHAR $PHAR_VERSION."
else
    push_status=$?
    latest=$(git ls-remote --exit-code origin "$branch_ref") || exit "$push_status"
    latest=${latest%%$'\t'*}
    if [ "$latest" != "$SOURCE_SHA" ]; then
        echo 'The branch advanced during publication; skipping this stale PHAR.'
        exit 0
    fi
    exit "$push_status"
fi
