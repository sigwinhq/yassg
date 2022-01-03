# Yet Another Static Site Generator

Start building a static site powered by [Twig](https://twig.symfony.com/) and [Encore](https://symfony.com/doc/current/frontend.html).

Use a YAML database to organize routes and data.

## Get started

1. create an empty folder

    ```shell
    mkdir yassg-test && cd yassg-test
    ```

2. require the package

    ```shell
    composer require sigwin/yassg
    ```

3. init the project

    ```shell
    vendor/sigwin/yassg/bin/yassg yassg:init
    ```

4. run a dev server:

    ```shell
    make start/dev
    ```

## Build the site

Pass the base URL to build

```shell
BASE_URL=https://example.com/subdir make build/clean
```

The output will be in the `public/` folder,
the contents of which needs to be deployed to the `BASE_URL`.

## Pages CI setup

Includes Gitlab CI / Gitlab Pages setup.
