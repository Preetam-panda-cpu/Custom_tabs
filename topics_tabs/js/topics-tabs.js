(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.topicsTabs = {
    attach: function (context) {
      once('topics-tabs', '[data-topics-tabs]', context).forEach(function (widget) {
        var buttons = widget.querySelectorAll('[data-tab]');
        var panels = widget.querySelectorAll('[data-panel]');

        function activateTab(key) {
          buttons.forEach(function (button) {
            var active = button.getAttribute('data-tab') === key;
            button.setAttribute('aria-selected', active ? 'true' : 'false');
            button.setAttribute('tabindex', active ? '0' : '-1');
            button.classList.toggle('is-active', active);
          });

          panels.forEach(function (panel) {
            var active = panel.getAttribute('data-panel') === key;
            panel.hidden = !active;
          });
        }

        buttons.forEach(function (button, index) {
          button.addEventListener('click', function () {
            activateTab(button.getAttribute('data-tab'));
          });

          button.addEventListener('keydown', function (event) {
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
              return;
            }
            event.preventDefault();
            var next = event.key === 'ArrowRight'
              ? (index + 1) % buttons.length
              : (index - 1 + buttons.length) % buttons.length;
            buttons[next].focus();
            activateTab(buttons[next].getAttribute('data-tab'));
          });
        });

        activateTab('top');
      });
    }
  };
})(Drupal, once);
