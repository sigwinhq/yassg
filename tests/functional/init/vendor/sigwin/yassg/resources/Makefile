BUILD_DIR=public
BASE_URL=https://example.com

self/build: ${BUILD_DIR}/assets/entrypoints.json 
	php ../../../bin/yassg yassg:generate --env prod $(BASE_URL)
self/init: self/clean 
	php ../../../bin/yassg yassg:init --demo
self/clean:
	rm -rf ../../../var/cache/* ../../../var/log/* ${BUILD_DIR} assets config node_modules templates var .gitignore Makefile package.json package-lock.json webpack.config.js
self/check: self/build
	lychee --verbose --offline --base ./${BUILD_DIR} ./${BUILD_DIR}

include ../../../resources/Makefile
