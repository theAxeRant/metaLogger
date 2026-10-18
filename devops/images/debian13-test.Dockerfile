FROM debian:13-slim

RUN apt-get update \
    && apt-get install -y --no-install-recommends iproute2 php-cli php-mbstring php-xml phpunit \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
ENV METALOGGER_TEST_DEBIAN13=1

# Mount symfony/ read-only at /app, including installed Composer dependencies.
# Use Debian's PHPUnit for compatibility with Debian 13's PHP runtime.
CMD ["phpunit", "--do-not-cache-result", "--bootstrap", "test/debian13-bootstrap.php", "test/"]
