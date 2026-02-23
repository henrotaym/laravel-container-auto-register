#!/bin/bash
docker compose build && \
./cli composer install && \
./cli bun install
