---
title: Images!
slug: images
publishedAt: "2022-07-20 12:35:00"
image: ./image.webp
---

| Column 1 | Column 2 | Column 3 |
| -------- | -------- | -------- |
| Data 1   | Data 2   | Data 3   |
| Data 4   | Data 5   | Data 6   |
| Data 7   | Data 8   | Data 9   |

This is a database lookup example: {{yassg_get('articles', '/hello-world.md').title}}

This is an asset lookup: {{ yassg_thumbnail(item.image) }}

![Logo]({{ yassg_thumbnail(item.image, {width: 260, height: 480}) }})

![Logo]({{ yassg_thumbnail(item.image, {width: 400, height: 200, gravity: "no"}) }})

{{ yassg_picture(item.image, {width: 400, height: 200, gravity: "no", attrs: {alt: "Hello", class: "prose", style: "border: 1px solid red"}, img_attrs: {class: "mb-rounded", fetchpriority: "high"}}) }}
