.SILENT:

PROJECT_ROOT := $(abspath $(patsubst %/,%,$(dir $(abspath $(lastword $(MAKEFILE_LIST))))))
BUILD_DIR := public
BASE_URL ?= "file://${PROJECT_ROOT}/${BUILD_DIR}"

include vendor/sigwin/yassg/resources/Makefile
