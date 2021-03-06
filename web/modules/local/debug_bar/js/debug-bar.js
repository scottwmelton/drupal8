(function ($) {

  Drupal.behaviors.debugBar = {
    attach: function (context, settings) {

      'use strict';

      var createCookie = function (name, value, days) {
        days = days || 100;
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = '; expires=' + date.toGMTString();
        document.cookie = name + '=' + value + expires + '; path=/;'
      };

      var readCookie = function (name) {
        var nameEQ = name + '=';
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
          var c = ca[i];
          while (c.charAt(0) == ' ') c = c.substring(1, c.length);
          if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
      };

      var debugBar = $('#debug-bar');

      if (debugBar.hasClass('debug-bar-float')) {
        var initialPosition = {
          left: parseInt(readCookie('debug_bar_position_left')),
          top: parseInt(readCookie('debug_bar_position_top'))
        };
        var defaultPosition = debugBar.position();

        debugBar.draggable({
          containment: 'document',
          create: function (event, ui) {
            $(this).css({
              top: initialPosition.top ? initialPosition.top : defaultPosition.top,
              left: initialPosition.left ? initialPosition.left : defaultPosition.left
            });
          },
          stop: function (event, ui) {
            createCookie('debug_bar_position_left', ui.position.left);
            createCookie('debug_bar_position_top', ui.position.top);
          }

        });
      }

      $('.debug-bar-link-hide').once().click(function () {
        debugBar.toggleClass('debug-bar-hidden');
        createCookie('debug_bar_hidden', 0 + debugBar.hasClass('debug-bar-hidden'));
        return false;
      });

    }
  };

})(jQuery);
