#!/bin/sh

set -e

if [ "$1" = 'run_tests' ]; then
  # Get the rest of the options from the command line
  shift
  echo -n "Running Composer update ..."
  composer update -W -qn
  echo -e " done"
  vendor/bin/phpunit --stop-on-failure "$@"
  exit $?
fi

# From the original entrypoint: first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
  set -- php "$@"
fi

exec "$@"
