include ../default.mk

self/test:
	cp public/sitemap* fixtures/
	sh -c "${PHPQA_DOCKER_COMMAND} diff -r fixtures/ public/"
