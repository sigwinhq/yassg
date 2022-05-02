APP_ROOT=$(abspath $(patsubst %/,%,$(dir $(abspath $(lastword $(MAKEFILE_LIST))))))
BUILD_DIR=public
BASE_URL=https://example.com

include ../../../vendor/sigwin/infra/resources/YASSG/default.mk

self/build: self/clean ${BUILD_DIR}/assets/entrypoints.json ## Self: build the app
	php ../../../bin/yassg yassg:generate --env prod $(BASE_URL) ${BUILD_OPTS}
self/init: self/clean ## Self: init the app via yassg:init
	php ../../../bin/yassg yassg:init --demo
self/clean:
	rm -rf ${BUILD_DIR}
self/check: self/build ## Self: check the build
	lychee --verbose --offline --base ./${BUILD_DIR} ./${BUILD_DIR}
self/serve: self/build ## Self: serve the build
	php -S localhost:9999 -t ${BUILD_DIR}/
