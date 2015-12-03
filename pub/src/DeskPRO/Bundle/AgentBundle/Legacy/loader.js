window.setupLegacy = (function() {
  function setupJquery() {
    $.ajaxPrefilter(function(options, originalOptions, jqXHR) {

      if (options.url.indexOf('old-agent') === -1) {
        return options;
      }

      var url = options.url;
      if (url.indexOf('?') == -1) {
        url += '?';
      } else {
        url += '&';
      }
      url += '_rt=' + DP_LEGACY_REQUEST_TOKEN;

      options.url = url;

      // Auto-retry with network errors

      options.numTries    = originalOptions.numTries || 3;
      options.retryCodes  = originalOptions.retryCodes || [500, 502, 503];
      options.tryNum      = originalOptions.tryNum || 1;

      if (window.DP_DEBUG) {
        options.retryCodes = [];
      }

      if (typeof originalOptions.numTries != 'undefined') {
        options.numTries = originalOptions.numTries;
      }

      if (options.numTries > 1 && options.tryNum == 1) {
        if (!originalOptions.error) {
          options.realError = function() {};
        } else {
          if (originalOptions.context) {
            options.realError = function(innerJqXhr, textStatus, httpError) { originalOptions.error.call(originalOptions.context, innerJqXhr, textStatus, httpError); };
          } else {
            options.realError = originalOptions.error;
          }
        }

        options.error = function(innerJqXhr, textStatus, httpError) {
          var retryable = false;

          if (textStatus === "error" && options.retryCodes.indexOf(innerJqXhr.status) !== -1 && (!options.type || options.type.toUpperCase() == 'GET')) {
            retryable = true;
          }

          if (retryable && options.tryNum < options.numTries) {
            options.tryNum++;
            return $.ajax(options);
          } else {
            return options.realError(innerJqXhr, textStatus, httpError);
          }
        };
      }

      return options;
    });
  }

  return function setupLegacy() {
    window.BASE_URL = DP_BASE_URL_RELATIVE + '/';

    setupJquery();

    //TODO
    window.DESKPRO_PERSON_PERMS = {};
    window.DESKPRO_ENABLE_KB_SHORTCUTS = false;
    window.DP_POLLER_INTERVAL = 99999999;

    window.DeskPRO_Window = new DeskPRO.Agent.Window({
      messageChanneler: {
        ajaxMessagesUrl: DP_BASE_URL_RELATIVE + '/get_messages.php',
        lastMessageId: 0
      },
      faviconCount: 0,
      desktopNotifications: false
    });

    window.DeskPRO_Window.initPage();
  }
})();
