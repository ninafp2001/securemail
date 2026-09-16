FROM php:8.2-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        ca-certificates \
        curl \
    && docker-php-ext-install curl \
    && a2enmod rewrite headers alias proxy proxy_http ssl \
    && rm -rf /var/lib/apt/lists/*

RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

WORKDIR /app

COPY . .

COPY deploy/apache-fly.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh \
    && mkdir -p /data data \
    && chown -R www-data:www-data /app

ENV PORT=8080
ENV FLY_APP_NAME=webmail-kinghost

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS "http://127.0.0.1:8080/health.php" | grep -qi "ok" || exit 1

CMD ["/usr/local/bin/docker-entrypoint.sh"]
