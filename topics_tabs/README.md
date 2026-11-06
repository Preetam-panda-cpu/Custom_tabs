# Topics Tabs — Drupal 11

A configurable Drupal block that displays two tabs: **Top Topics** and **Most Read**.

## Install

1. Copy the `topics_tabs` folder into `web/modules/custom/` (or `modules/custom/` for a non-Composer-root layout).
2. Enable it with Drush:

   ```bash
   drush en topics_tabs -y
   drush cr
   ```

3. Go to **Structure → Block layout** (`/admin/structure/block`).
4. Place the **Topics tabs** block in the required region.
5. Open the block configuration and edit the two topic lists.

## Configure links

Enter one item per line using:

```text
Visible topic title|/internal-path
External article title|https://example.com/article
```

The module ships with sample entries based on the supplied screenshot. Replace the sample URLs with your real Drupal paths or article URLs.

## Features

- Drupal 11 block plugin
- Separate editable lists for both tabs
- Keyboard-accessible tab switching
- Drupal behaviors with `once()`
- Responsive CSS styled to resemble the reference image
