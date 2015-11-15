import Moment from "moment";

/**
 * @deprecated
 * @see Tasks/List/View/Calendar
 */
export default class Calendar {
  constructor(options) {
    options = options || {};

    this.startDate = options.startDate;
    this.endDate = options.endDate;
    this.maxInterval = options.maxInterval;
    this.maxConstraint = options.maxConstraint;
    this.siblingMonths = options.siblingMonths;
    this.weekStart = options.weekStart;

    if (this.weekStart === undefined) {
      this.weekStart = 0;
    }

    this.date = new Moment();
  }

  getCalendar(year, month) {
    this.date.year(year);
    this.date.month(month);
    this.date.date(1);

    let calendar = [],
        firstDay = this.date.day(),     // Get day of week
        firstDate = -(((7 - this.weekStart) + firstDay) % 7),
        lastDate = this.daysInMonth(year, month),
        lastDay = ((lastDate - firstDate) % 7),
        lastDayLastMonth = this.daysInMonth(year, month - 1),
        i = firstDate,
        max = (lastDate - i) + (lastDay !== 0 ? 7 - lastDay : 0) + firstDate,
        currentDay,
        currentDate,
        currentDateObject,
        otherMonth,
        otherYear;

    while (i < max) {
      currentDate = i + 1;
      currentDay = ((i < 1 ? 7 + i : i) + firstDay) % 7;

      if (currentDate < 1 || currentDate > lastDate) {
        if (this.siblingMonths) {
          if (currentDate < 1) {
            otherMonth = month - 1;
            otherYear = year;
            if (otherMonth < 0) {
              otherMonth = 11;
              otherYear --;
            }
            currentDate = lastDayMonth + currentDate;
          } else if ( currentDate > lastDate ) {
  					otherMonth = month + 1;
  					otherYear = year;
  					if ( otherMonth > 11 ) {
  						otherMonth = 0;
  						otherYear ++;
  					}
  					currentDate = i - lastDate + 1;
  				}

  				currentDateObject = {
  					day: currentDate,
  					weekDay: currentDay,
  					month: otherMonth,
  					year: otherYear,
  					siblingMonth: true
  				};
        } else {
          currentDateObject = false;
        }
      } else {
        currentDateObject = {
          day: currentDate,
          weekDay: currentDay,
          month: month,
          year: year
        };
      }

      if (currentDateObject && this.startDate) {
        currentDateObject.selected = this.isDateSelected(currentDateObject);
      }

      calendar.push(currentDateObject);
      i++;
    }

    return calendar;
  }

  isDateSelected(date) {
    if (date.year == this.startDate.year && date.month == this.startDate.month && date.day == this.startDate.day) {
      return true;
    } else if (this.endDate) {
      if (date.year == this.startDate.year && date.month == this.startDate.month && date.day < this.startDate.day) {
  			return false;
  		}
  		else if (date.year == this.endDate.year && date.month == this.endDate.month && date.day > this.endDate.day) {
  			return false;
  		}
  		else if (date.year == this.startDate.year && date.month < this.startDate.month) {
  			return false;
  		}
  		else if (date.year == this.endDate.year && date.month > this.endDate.month) {
  			return false;
  		}
  		else if (date.year < this.startDate.year) {
  			return false;
  		}
  		else if (date.year > this.endDate.year) {
  			return false;
  		}
  		return true;
    }
  }

  setStartDate(date) {
    this.startDate = date;
  }

  setEndDate(date) {
    this.endDate = date;
  }

  interval(date1, date2) {
    let oDate1 = new Moment(),
        oDate2 = new Moment();

    oDate1.year(date1.year);
    oDate1.month(date1.month);
    oDate1.date(date1.day);

    oDate2.year(date2.year);
    oDate2.month(date2.month);
    oDate2.date(date2.day);

    return oDate2.diff(oDate1, 'days');
  }

  daysInMonth(year, month) {
    const moment = new Moment([year, month]);
    return moment.daysInMonth();
  }
}
