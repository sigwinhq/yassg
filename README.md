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

## Features

### Custom Social Images

Generate dynamic social images (Open Graph, Twitter Cards) from Twig templates using SVG.

#### Configuration

Set a default social image template in your configuration:

```yaml
# config/packages/yassg_routes.yaml
sigwin_yassg:
    social_image_template: 'social/default.svg.twig'
```

#### Per-route Configuration

Override the template for specific routes:

```yaml
sigwin_yassg:
    routes:
        article:
            path: /article/{slug}
            options:
                social_image_template: 'social/article.svg.twig'
```

#### Usage in Templates

Generate a social image URL in your Twig templates:

```twig
{% set social_image_url = yassg_social_image('social/custom.svg.twig') %}
<meta property="og:image" content="{{ social_image_url }}">
```

Or use the default template:

```twig
{% set social_image_url = yassg_social_image() %}
```

#### Creating SVG Templates

Create SVG templates that receive the full Twig context:

```svg
{# templates/social/article.svg.twig #}
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630">
    <rect width="1200" height="630" fill="#1a202c"/>
    <text x="600" y="315" font-family="Arial" font-size="64" fill="#ffffff" text-anchor="middle">
        {{ article.title }}
    </text>
</svg>
```

The SVG is automatically processed through ImgProxy and converted to WebP (or other formats) during the build process.

#### Custom Options

Customize image dimensions and format:

```twig
{% set social_image_url = yassg_social_image('social/custom.svg.twig', {
    width: 1200,
    height: 630,
    format: 'png'
}) %}
```

