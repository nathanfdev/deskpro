define([
  'moment'
], (
  moment
) => {
  /*
   * Date formatter service
   */
  class Admin_Main_Service_DpDate {

    constructor() {
      // todo: get formats from App Settings
      this.formats = {
        full:      'ddd, Do MMM YYYY',
        fulltime:  'ddd, Do MMM YYYY h:mma',
        day:       'MMM D YYYY',
        day_short: 'MMM D',
        time:      'h:mm a'
      };

      this.default = 'fulltime';
    }


    local(date) {
      if (!date) { return date; }

      if (typeof date === 'string') {
        date = moment.utc(date).toDate();
      } else if (date instanceof Date) {
        date = moment.utc(date.getTime()).toDate();
      }

      return date;
    }


    format(date, format) {
      if (!format) { format = this.default; }
      if (!this.formats[format]) { return null; }

      date = this.local(date);
      if (!date) { return null; }

      return moment(date).format(this.formats[format]);
    }
  }
  return Admin_Main_Service_DpDate;
});
