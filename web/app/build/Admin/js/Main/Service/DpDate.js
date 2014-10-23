(function() {
  define(['moment'], function(moment) {

    /*
    	 * Date formatter service
     */
    var Admin_Main_Service_DpDate;
    return Admin_Main_Service_DpDate = (function() {
      function Admin_Main_Service_DpDate() {
        this.formats = {
          full: 'ddd, Do MMM YYYY',
          fulltime: 'ddd, Do MMM YYYY h:mma',
          day: 'MMM D YYYY',
          day_short: 'MMM D',
          time: 'h:mm a'
        };
        this["default"] = 'fulltime';
      }

      Admin_Main_Service_DpDate.prototype.local = function(date) {
        if (!date) {
          return date;
        }
        if ('string' === typeof date) {
          date = moment.utc(date).toDate();
        } else if (date instanceof Date) {
          date = moment.utc(date.getTime()).toDate();
        }
        return date;
      };

      Admin_Main_Service_DpDate.prototype.format = function(date, format) {
        if (!format) {
          format = this["default"];
        }
        if (!this.formats[format]) {
          return null;
        }
        date = this.local(date);
        if (!date) {
          return null;
        }
        return moment(date).format(this.formats[format]);
      };

      return Admin_Main_Service_DpDate;

    })();
  });

}).call(this);

//# sourceMappingURL=DpDate.js.map
