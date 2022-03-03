BUILD_DIR=public
BASE_URL=https://example.com

self/build: self/clean ${BUILD_DIR}/assets/entrypoints.json 
	php ../../../bin/yassg yassg:generate --env prod $(BASE_URL) ${BUILD_OPTS}
self/init: self/clean 
	php ../../../bin/yassg yassg:init --demo
self/clean:
	rm -rf ${BUILD_DIR} var
self/check: self/build
	lychee --verbose --offline --base ./${BUILD_DIR} ./${BUILD_DIR}
self/serve: self/build
	php -S localhost:9999 -t ${BUILD_DIR}/

include ../../../resources/Makefile
