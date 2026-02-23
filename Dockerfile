FROM php:8.3-cli-alpine AS cli

COPY --from=composer:2.5.8 /usr/bin/composer /usr/bin/composer

WORKDIR /opt/apps/app

FROM oven/bun:1.3 AS bun

WORKDIR /opt/apps/app

COPY . .
