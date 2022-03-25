BASE_URL ?= file://localhost${APP_ROOT}/${BUILD_DIR}
BUILD_OPTS ?=

start/dev: dev
dev: clean
	@make dev/assets dev/server -j2
dev/server: vendor index.php
	symfony server:start --no-tls --document-root=. --port=${APP_PORT}
dev/assets: node_modules
	node_modules/.bin/encore dev-server
index.php:
	ln -s vendor/sigwin/yassg/web/index.php

build: ${BUILD_DIR}/assets/entrypoints.json vendor 
	php vendor/sigwin/yassg/bin/yassg yassg:generate --env prod $(BASE_URL) ${BUILD_OPTS}
.PHONY: build

${BUILD_DIR}:
	mkdir -p ${BUILD_DIR}
${BUILD_DIR}/assets/entrypoints.json: | ${BUILD_DIR} node_modules 
	BASE_URL=${BASE_URL} node_modules/.bin/encore production

clean:
	rm -rf var/cache/* var/log/* ${BUILD_DIR}
.PHONY: clean
node_modules:
	npm install
vendor:
	composer install
