self/build: self/clean self/init ${BUILD_DIR}/assets/entrypoints.json 
	php ../../bin/yassg yassg:generate --env prod $(BASE_URL)
self/clean:
	rm -rf ../../var/cache/* ../../var/log/* ${BUILD_DIR}
self/init: self/clean 
	php ../../bin/yassg yassg:init --demo

include ../../resources/Makefile
