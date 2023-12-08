---
title: Images!
slug: images
publishedAt: "2022-07-20 12:35:00"
image: assets/images/sigwin.svg
---

| Column 1 | Column 2 | Column 3 |
| -------- | -------- | -------- |
| Data 1   | Data 2   | Data 3   |
| Data 4   | Data 5   | Data 6   |
| Data 7   | Data 8   | Data 9   |

This is a database lookup example: {{yassg_get('articles', '/hello-world.md').title}}

This is an asset lookup: {{asset(item.image)}}

![Logo]({{ asset(item.image) }})

