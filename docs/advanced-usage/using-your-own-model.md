---
title: Using your own model
weight: 2
---

By default this package uses the `Spatie\ServerMonitor\Models\Check` model. If you want to add some extra functionality you can specify your own model in the `check_model` key of the config file. The only requirement for your custom model is that is should extend `Spatie\ServerMonitor\Models\Check`.
