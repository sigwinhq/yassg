BASE_URL ?= file://${APP_ROOT}/${BUILD_DIR}

start/dev: dev
dev: clean
	@make dev/assets dev/server -j2
dev/server: vendor index.php
	php -S localhost:${APP_PORT}
dev/assets: node_modules
	node_modules/.bin/encore dev-server
index.php:
	ln -s vendor/sigwin/yassg/web/index.php

build: ${BUILD_DIR}/assets/entrypoints.json vendor 
	php vendor/sigwin/yassg/bin/yassg yassg:generate --env prod $(BASE_URL)
build/clean: clean ${BUILD_DIR}/assets/entrypoints.json vendor
	php vendor/sigwin/yassg/bin/yassg yassg:generate --env prod $(BASE_URL)
.PHONY: build build/clean

${BUILD_DIR}:
	mkdir -p ${BUILD_DIR}
${BUILD_DIR}/assets/entrypoints.json: | ${BUILD_DIR} node_modules 
	BASE_URL=${BASE_URL} node_modules/.bin/encore production

clean:
	rm -rf cache ${BUILD_DIR}
.PHONY: clean
node_modules:
	npm install
vendor:
	composer install
