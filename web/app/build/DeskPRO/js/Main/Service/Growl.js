(function() {
  define(['toastr'], function(toastr) {
    var DeskPRO_Main_Service_Growl;
    return DeskPRO_Main_Service_Growl = (function() {
      function DeskPRO_Main_Service_Growl(options) {
        toastr.options = _.defaults(options || {}, {
          closeButton: true,
          positionClass: 'toast-bottom-right',
          showDuration: 250,
          hideDuration: 500,
          timeOut: 3000,
          extendedTimeOut: 600,
          showEasing: "swing",
          hideEasing: "linear",
          showMethod: "slideDown",
          hideMethod: "fadeOut"
        });
      }


      /**
      		* Gets a title/message from a 'content' param
        	*
        	* @return {Array}
       */

      DeskPRO_Main_Service_Growl.prototype._getContent = function(content) {
        var message, title;
        if (_.isObject(content)) {
          title = content.title || null;
          message = content.message || null;
        } else {
          title = null;
          message = content;
        }
        return [title, message];
      };


      /**
      		* Gets an options array from a 'options' param
        	*
        	* @return {Object}
       */

      DeskPRO_Main_Service_Growl.prototype._getOptions = function(options) {
        if (!options) {
          return {};
        }
        if (_.isFunction(options)) {
          options = {
            onclick: options
          };
        }
        return options;
      };


      /**
      		* Shows an error notif
        	*
        	* @param {String/Object} A string message, or an object with 'title' and 'message' properties
        	* @param {Object/Function} An object of options, or a simple Function callback for a click handler
        	* @return {notify}
       */

      DeskPRO_Main_Service_Growl.prototype.error = function(content, options) {
        var message, title, _ref;
        _ref = this._getContent(content), title = _ref[0], message = _ref[1];
        options = this._getOptions(options);
        return toastr.error(message, title, options);
      };


      /**
      		* Shows an info notif
        	*
        	* @param {String/Object} A string message, or an object with 'title' and 'message' properties
        	* @param {Object/Function} An object of options, or a simple Function callback for a click handler
        	* @return {notify}
       */

      DeskPRO_Main_Service_Growl.prototype.info = function(content, options) {
        var message, title, _ref;
        _ref = this._getContent(content), title = _ref[0], message = _ref[1];
        options = this._getOptions(options);
        return toastr.info(message, title, options);
      };


      /**
      		* Shows a success notif
        	*
        	* @param {String/Object} A string message, or an object with 'title' and 'message' properties
        	* @param {Object/Function} An object of options, or a simple Function callback for a click handler
        	* @return {notify}
       */

      DeskPRO_Main_Service_Growl.prototype.success = function(content, options) {
        var message, title, _ref;
        _ref = this._getContent(content), title = _ref[0], message = _ref[1];
        options = this._getOptions(options);
        return toastr.success(message, title, options);
      };


      /**
      		* Shows a warning notif
        	*
        	* @param {String/Object} A string message, or an object with 'title' and 'message' properties
        	* @param {Object/Function} An object of options, or a simple Function callback for a click handler
        	* @return {notify}
       */

      DeskPRO_Main_Service_Growl.prototype.warning = function(content, options) {
        var message, title, _ref;
        _ref = this._getContent(content), title = _ref[0], message = _ref[1];
        options = this._getOptions(options);
        return toastr.warning(message, title, options);
      };


      /**
      		* Clears all open notifs
       */

      DeskPRO_Main_Service_Growl.prototype.clearAll = function() {
        return toastr.clear();
      };


      /**
      		* Clears a specific notif
        	*
        	* @param {notify} notify
       */

      DeskPRO_Main_Service_Growl.prototype.clearNotif = function(notify) {
        return toastr.clear(notify);
      };

      return DeskPRO_Main_Service_Growl;

    })();
  });

}).call(this);

//# sourceMappingURL=Growl.js.map
