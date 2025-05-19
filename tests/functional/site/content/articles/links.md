---
title: Links! Links! Links!
slug: links
publishedAt: "2025-05-19 13:30:00"
---

{% set article = yassg_get('articles', "/hello-world.md") %}

This is a link to [{{ article.title }}]({{ yassg_url(article) }})
