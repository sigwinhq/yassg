---
title: Images!
slug: images
publishedAt: "2022-07-20 12:35:00"
image: assets/images/sigwin.svg
---

This is a database lookup example: {{yassg_get('articles', '/hello-world.md').title}}

This is an asset lookup: {{asset(item.image)}}

![Logo]({{ asset(item.image) }})

