define(['toastr', 'underscore'], (toastr, _) => {
  class DeskPRO_Main_Service_Growl {
    constructor(options) {
      toastr.options = _.defaults(options || {}, {
        closeButton:     true,
        positionClass:   'toast-bottom-right',
        showDuration:    250,
        hideDuration:    500,
        timeOut:         3000,
        extendedTimeOut: 600,
        showEasing:      'swing',
        hideEasing:      'linear',
        showMethod:      'slideDown',
        hideMethod:      'fadeOut'
      });
    }


    /**
    * Gets a title/message from a 'content' param
      *
      * @return {Array}
    */
    _getContent(content) {
      let message,
        title;
      if (_.isObject(content)) {
        title = content.title || null;
        message = content.message || null;
      } else {
        title   = null;
        message = content;
      }

      return [title, message];
    }


    /**
    * Gets an options array from a 'options' param
      *
      * @return {Object}
    */
    _getOptions(options) {
      if (!options) { return {}; }

      if (_.isFunction(options)) {
        options = {
          onclick: options
        };
      }

      return options;
    }


    /**
    * Shows an error notif
      *
      * @param {String/Object} A string message, or an object with 'title' and 'message' properties
      * @param {Object/Function} An object of options, or a simple Function callback for a click handler
      * @return {notify}
    */
    error(content, options) {
      const [title, message] = Array.from(this._getContent(content));
      options = this._getOptions(options);
      return toastr.error(message, title, options);
    }


    /**
    * Shows an info notif
      *
      * @param {String/Object} A string message, or an object with 'title' and 'message' properties
      * @param {Object/Function} An object of options, or a simple Function callback for a click handler
      * @return {notify}
    */
    info(content, options) {
      const [title, message] = Array.from(this._getContent(content));
      options = this._getOptions(options);
      return toastr.info(message, title, options);
    }


    /**
    * Shows a success notif
      *
      * @param {String/Object} A string message, or an object with 'title' and 'message' properties
      * @param {Object/Function} An object of options, or a simple Function callback for a click handler
      * @return {notify}
    */
    success(content, options) {
      const [title, message] = Array.from(this._getContent(content));
      options = this._getOptions(options);
      return toastr.success(message, title, options);
    }


    /**
    * Shows a warning notif
      *
      * @param {String/Object} A string message, or an object with 'title' and 'message' properties
      * @param {Object/Function} An object of options, or a simple Function callback for a click handler
      * @return {notify}
    */
    warning(content, options) {
      const [title, message] = Array.from(this._getContent(content));
      options = this._getOptions(options);
      return toastr.warning(message, title, options);
    }


    /**
    * Clears all open notifs
    */
    clearAll() {
      return toastr.clear();
    }


    /**
    * Clears a specific notif
      *
      * @param {notify} notify
    */
    clearNotif(notify) {
      return toastr.clear(notify);
    }
  }

  return DeskPRO_Main_Service_Growl;
});
