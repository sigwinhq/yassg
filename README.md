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

Generate dynamic social images (Open Graph, Twitter Cards) from SVG templates. Each route can render a `.svg` version that is then processed through ImgProxy.

#### How It Works

1. **SVG URLs**: Any Linkable entity can generate an SVG URL using `yassg_svg_url(entity)`
2. **Template Rendering**: The SVG template at `pages/{route}.svg.twig` receives the same context as the HTML page
3. **ImgProxy Processing**: Use `yassg_thumbnail()` to process the SVG through ImgProxy
4. **Build Time**: Images are generated and optimized during the build process

#### Usage in Templates

Define a `social_image` block in your page template that uses `yassg_svg_url()`:

```twig
{# templates/pages/article.html.twig #}
{% extends 'layout.html.twig' %}

{% set article = yassg_find_one_by('articles', {condition: {'item.slug': slug}}) %}

{% block title %}{{ article.title }}{% endblock %}

{% block social_image %}{{ yassg_svg_url(article) }}{% endblock %}

{% block body %}
    {# ... #}
{% endblock %}
```

In your layout template, use the block to generate meta tags:

```twig
{# templates/layout.html.twig #}
{% block social_image %}{% endblock %}
{% set social_image_url = block('social_image') %}
{% if social_image_url is not empty %}
<meta property="og:image" content="{{ yassg_thumbnail(absolute_url(social_image_url)) }}">
<meta name="twitter:image" content="{{ yassg_thumbnail(absolute_url(social_image_url)) }}">
<meta name="twitter:card" content="summary_large_image">
{% endif %}
```

#### Creating SVG Templates

Create SVG templates in `templates/pages/{route}.svg.twig`:

```svg
{# templates/pages/article.svg.twig #}
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630">
    <rect width="1200" height="630" fill="#1a202c"/>
    <text x="600" y="315" font-family="Arial" font-size="64" fill="#ffffff" text-anchor="middle">
        {{ article.title }}
    </text>
    <text x="600" y="380" font-family="Arial" font-size="24" fill="#cccccc" text-anchor="middle">
        {{ article.publishedAt|date('F j, Y') }}
    </text>
</svg>
```

The SVG template receives the same variables as the HTML template (article, page, etc.).

#### Making Your Model Linkable

To use `yassg_svg_url()`, your model must implement the `Linkable` interface:

```php
use Sigwin\YASSG\Linkable;

final class Article implements Linkable
{
    public string $title;
    public string $slug;
    
    public function getLinkRouteName(): string
    {
        return 'article';
    }
    
    public function getLinkRouteParameters(): array
    {
        return ['slug' => $this->slug];
    }
}
```

#### Composing with Other Assets

Since SVG templates can use any Twig functions, you can include other assets:

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630">
    <image href="{{ yassg_thumbnail(article.image, {width: 1200, height: 630}) }}" width="1200" height="630"/>
    <rect width="1200" height="630" fill="rgba(0,0,0,0.5)"/>
    <text x="600" y="315" font-size="64" fill="#fff" text-anchor="middle">
        {{ article.title }}
    </text>
</svg>
```

