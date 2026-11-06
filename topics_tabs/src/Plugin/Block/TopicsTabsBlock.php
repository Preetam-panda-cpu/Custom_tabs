<?php

namespace Drupal\topics_tabs\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a configurable Topics Tabs block.
 *
 * @Block(
 *   id = "topics_tabs_block",
 *   admin_label = @Translation("Topics tabs"),
 *   category = @Translation("Custom")
 * )
 */
class TopicsTabsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'top_topics' => implode("\n", [
        'Why US Senate races in Alaska and Mississippi haven’t been called yet, and how long it could take|https://www.hindustantimes.com/',
        'Ramayana movie review|https://www.hindustantimes.com/',
        'Three-storey building|https://example.com/orbit-determination',
        'Rahul Gandhi’s ‘alcohol’ remark on |https://www.hindustantimes.com/',
        'Chawri Bazar building collapse|https://www.hindustantimes.com/',
      ]),
      'most_read' => implode("\n", [
        'Latest research and articles|https://example.com/latest-research',
        'Navigation technology updates|https://example.com/navigation-updates',
        'Global positioning systems explained|https://example.com/gps-explained',
      ]),
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['topics_help'] = [
      '#type' => 'item',
      '#title' => $this->t('Configure topic links'),
      '#description' => $this->t('Enter one topic per line in the format: Title|URL. Use an internal path such as /node/1 or a full https:// URL.'),
    ];

    $form['top_topics'] = [
      '#type' => 'textarea',
      '#title' => $this->t('News'),
      '#default_value' => $this->configuration['top_topics'],
      '#rows' => 8,
      '#required' => FALSE,
    ];

    $form['most_read'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Article'),
      '#default_value' => $this->configuration['most_read'],
      '#rows' => 8,
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['top_topics'] = $form_state->getValue('top_topics');
    $this->configuration['most_read'] = $form_state->getValue('most_read');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $instance_id = 'topics-tabs-' . substr(hash('sha256', $this->getPluginId() . serialize($this->configuration)), 0, 10);
    $tabs = [
      'top' => [
        'label' => $this->t('News'),
        'items' => $this->parseItems($this->configuration['top_topics'] ?? ''),
      ],
      'read' => [
        'label' => $this->t('Article'),
        'items' => $this->parseItems($this->configuration['most_read'] ?? ''),
      ],
    ];

    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['topics-tabs'],
        'data-topics-tabs' => '',
      ],
      '#attached' => [
        'library' => ['topics_tabs/topics_tabs'],
      ],
    ];

    $tab_buttons = [];
    foreach ($tabs as $key => $tab) {
      $active = $key === 'top';
      $tab_buttons[$key] = [
        '#type' => 'html_tag',
        '#tag' => 'button',
        '#value' => $tab['label'],
        '#attributes' => [
          'type' => 'button',
          'class' => ['topics-tabs__tab'],
          'id' => $instance_id . '-' . $key . '-tab',
          'role' => 'tab',
          'aria-controls' => $instance_id . '-' . $key . '-panel',
          'aria-selected' => $active ? 'true' : 'false',
          'tabindex' => $active ? '0' : '-1',
          'data-tab' => $key,
        ],
      ];
    }

    $build['tab_list'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['topics-tabs__tab-list'],
        'role' => 'tablist',
        'aria-label' => $this->t('Topic lists'),
      ],
      'children' => $tab_buttons,
    ];

    foreach ($tabs as $key => $tab) {
      $active = $key === 'top';
      $links = [];
      foreach ($tab['items'] as $item) {
        $links[] = [
          '#type' => 'link',
          '#title' => $item['title'],
          '#url' => $item['url'],
          '#attributes' => ['class' => ['topics-tabs__link']],
        ];
      }

      $build[$key . '_panel'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['topics-tabs__panel'],
          'id' => $instance_id . '-' . $key . '-panel',
          'role' => 'tabpanel',
          'aria-labelledby' => $instance_id . '-' . $key . '-tab',
          'data-panel' => $key,
        ] + ($active ? [] : ['hidden' => 'hidden']),
        'items' => $links ?: [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('No topics have been added yet.'),
          '#attributes' => ['class' => ['topics-tabs__empty']],
        ],
      ];
    }

    return $build;
  }

  /**
   * Parses configured lines into safe link definitions.
   */
  protected function parseItems($value) {
    $items = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) $value) as $line) {
      $line = trim($line);
      if ($line === '' || strpos($line, '|') === FALSE) {
        continue;
      }
      [$title, $path] = array_map('trim', explode('|', $line, 2));
      if ($title === '' || $path === '') {
        continue;
      }

      try {
        $url = str_starts_with($path, '/') && !str_starts_with($path, '//')
          ? Url::fromUserInput($path)
          : Url::fromUri($path);
        $items[] = ['title' => $title, 'url' => $url];
      }
      catch (\InvalidArgumentException $exception) {
        // Ignore invalid URLs instead of breaking the block.
      }
    }
    return $items;
  }

}
