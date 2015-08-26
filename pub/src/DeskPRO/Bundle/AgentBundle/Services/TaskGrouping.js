import Moment from "moment";

export default class TaskGrouping {
  constructor(projects = [], departments = {}, teams = {}, agents = {}, lists = {}, links = {}, tickets = {}) {
    this.projects = projects;
    this.departments = departments;
    this.teams = teams;
    this.agents = agents;
    this.lists = lists;
    this.links = links;
    this.tickets = tickets;

    this.futureDates = [
      { key: 'overdue', title: 'Overdue', value: false },
      { key: 'hour', title: 'This Hour', value: Moment().endOf('hour').format() },
      { key: 'day', title: 'Today', value: Moment().endOf('day').format() },
      { key: 'tomorrow', title: 'Tomorrow', value: Moment().endOf('day').add(1, 'd').format() },
      { key: 'week', title: 'This Week', value: Moment().endOf('week').format() },
      { key: 'nextweek', title: 'Next Week', value: Moment().endOf('week').add(7, 'd').format() },
      { key: 'month', title: 'This Month', value: Moment().endOf('month').format() },
      { key: 'nextmonth', title: 'Next Month', value: Moment().endOf('month').add(1, 'M').format() },
      { key: 'year', title: 'This Year', value: Moment().endOf('year').format() },
      { key: 'forever', title: 'Other', value: false }
    ];

    this.pastDates = [
      { key: 'hour', title: 'This Hour', value: Moment().startOf('hour').format() },
      { key: 'day', title: 'Today', value: Moment().startOf('day').format() },
      { key: 'tomorrow', title: 'Yesterday', value: Moment().startOf('day').subtract(1, 'd').format() },
      { key: 'week', title: 'This Week', value: Moment().startOf('week').format() },
      { key: 'nextweek', title: 'Last Week', value: Moment().startOf('week').subtract(7, 'd').format() },
      { key: 'month', title: 'This Month', value: Moment().startOf('month').format() },
      { key: 'nextmonth', title: 'Last Month', value: Moment().startOf('month').subtract(1, 'M').format() },
      { key: 'year', title: 'This Year', value: Moment().startOf('year').format() },
      { key: 'forever', title: 'Older', value: false }
    ];
  }

  /**
   * Get the divider to return according to the grouping type
   * @param object object
   * @param grouping string
   * @returns {{objectDivider: string, textDisplay: string}}
   */
  getDivider(object, grouping = false)
  {
    let results = false;

    switch(grouping) {
      case 'due':
        results = this.getDueDividers(object);
        break;
      case 'created':
        results = this.getCreatedDividers(object);
        break;
      case 'assignee':
        results = this.getAssigneeDividers(object);
        break;
      case 'project':
        results = this.getProjectDividers(object);
        break;
      case 'list':
        results = this.getListDividers(object);
        break;
      case 'creator':
        results = this.getCreatorDividers(object);
        break;
      case 'done':
        results = this.getDoneDateDividers(object);
        break;
      case 'labels':
        results = this.getLabelDividers(object);
        break;
      case 'ticket':
        results = this.getTicketDividers(object);
        break;
      default:
        return false;
    }

    return results;
  }

  /**
   * Returns an ID for the column / group
   * @param object
   * @param grouping
   * @returns string
   */
  getGroup(object, grouping)
  {
    let result = false;

    switch(grouping) {
      case 'due':
        result = this.getDueDivider(object).objectDivider;
        break;
      case 'created':
        result = this.getCreatedDivider(object).objectDivider;
        break;
      case 'assignee':
        result = this.getAssigneeDivider(object).objectDivider;
        break;
      case 'project':
        result = 'project_' + object.project;
        break;
      case 'list':
        result = 'list_' + object.list;
        break;
      case 'creator':
        result = 'creator_' + object.creator;
        break;
      case 'done':
        result = this.getDoneDateDivider(object).objectDivider;
        break;
      case 'labels':
        result = this.getLabelDivider(object).objectDivider;
        break;
      case 'ticket':
        result = this.getTicketDivider(object).objectDivider;
        break;
      default:
        return false;
    }

    return result;
  }

  /**
   * Get the details of the groupings according to the type to group by
   * Returns and array of objects, each object containing ID, a title, a field to update, and a value to update with
   * @param type
   * @returns {Array}
   */
  getRawGroupings(type)
  {
    let returnGroups = [];
    switch (type) {
      case 'list':
        this.lists.forEach((listObject) => {
          returnGroups.push({
            id: listObject.id,
            title: listObject.title,
            updateField: 'list',
            updateValue: listObject.id,
            key: 'list_' + listObject.id
          });

        });
        break;
      case 'project':
        this.projects.forEach((project) => {
          returnGroups.push({
            id: project.id,
            title: project.title,
            updateField: 'project',
            updateValue: project.id,
            key: 'project_' + project.id
          });
        });
        break;
      case 'due':
        this.futureDates.forEach((date) => {
          returnGroups.push({
            id: date.key,
            title: date.title,
            updateField: 'date_due',
            updateValue: date.value,
            key: date.key
          });
        });
        break;
      case 'done':
      case 'created':
        this.pastDates.forEach((date) => {
          returnGroups.push({
            id: date.key,
            title: date.title,
            updateField: 'date_due',
            updateValue: date.value,
            key: date.key
          });
        });
    }

    return returnGroups;
  }

  /**
   * Get the divider to use when grouping by due date
   * @param object
   * @returns {{objectDivider: string, textDisplay: string}}
   */
  getDueDivider(object)
  {
    let objectDivider = false;
    let textDisplay = false;

    switch(true) {
      case (Moment(object.date_due).isBefore()):
        objectDivider = 'overdue';
        textDisplay = 'Overdue';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('hour'))):
        objectDivider = 'hour';
        textDisplay = 'This Hour';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('day'))):
        objectDivider = 'day';
        textDisplay = 'Today';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('day').add(1, 'd'))):
        objectDivider = 'tomorrow';
        textDisplay = 'Tomorrow';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('week'))):
        objectDivider = 'week';
        textDisplay = 'This Week';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('week').add(7, 'd'))):
        objectDivider = 'nextweek';
        textDisplay = 'Next Week';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('month'))):
        objectDivider = 'month';
        textDisplay = 'This Month';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('month').add(1, 'M'))):
        objectDivider = 'nextmonth';
        textDisplay = 'Next Month';
        break;
      case (Moment(object.date_due).isBefore(Moment().endOf('year'))):
        objectDivider = 'year';
        textDisplay = 'This Year';
        break;
      default:
        objectDivider = 'forever';
        textDisplay = 'Other';
        break;
    }

    return {
      objectDivider: objectDivider,
      textDisplay: textDisplay
    };
  }

  /**
   * Get divider to use when grouping by created date
   * @param object
   * @returns {{objectDivider: string, textDisplay: string}}
   */
  getCreatedDivider(object)
  {
    let objectDivider = false;
    let textDisplay = false;

    switch(true) {
      case (Moment(object.date_created).isAfter(Moment().startOf('hour'))):
        objectDivider = 'hour';
        textDisplay = 'This Hour';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('day'))):
        objectDivider = 'day';
        textDisplay = 'Today';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('day').subtract(1, 'd'))):
        objectDivider = 'tomorrow';
        textDisplay = 'Yesterday';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('week'))):
        objectDivider = 'week';
        textDisplay = 'This Week';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('week').subtract(7, 'd'))):
        objectDivider = 'lastweek';
        textDisplay = 'Last Week';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('month'))):
        objectDivider = 'month';
        textDisplay = 'This Month';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('month').subtract(1, 'M'))):
        objectDivider = 'lastmonth';
        textDisplay = 'Last Month';
        break;
      case (Moment(object.date_created).isAfter(Moment().startOf('year'))):
        objectDivider = 'year';
        textDisplay = 'This Year';
        break;
      default:
        objectDivider = 'forever';
        textDisplay = 'Older';
        break;
    }

    return {
      objectDivider: objectDivider,
      textDisplay: textDisplay
    };
  }

  /**
   * Get divider to use when grouping by assignee
   * @param object
   * @returns {{objectDivider: string, textDisplay: string}}
   */
  getAssigneeDivider(object)
  {
    let assignee = false;
    if (object.agents.length > 0) {
      assignee = this.agents[object.agents[0]].name;
    } else if (object.teams.length > 0) {
      assignee = this.teams[object.teams[0]].name;
    } else if (object.departments.length > 0) {
      assignee = this.departments[object.departments[0]].title;
    }

    if (assignee === false) {
      return {
        objectDivider: 'none',
        textDisplay: 'Unassigned'
      }
    }

    return {
      objectDivider: assignee,
      textDisplay: assignee
    }
  }

  /**
   * Get divider to use when grouping by assignee
   * @param object
   * @returns {{objectDivider: string, textDisplay: string}}
   */
  getProjectDivider(object)
  {
    let project = false;
    if (object.project) {
      project = this.projects[object.project].title;
    }

    if (project === false) {
      return {
        objectDivider: 'none',
        textDisplay: 'No Project'
      }
    }

    return {
      objectDivider: project,
      textDisplay: project
    }
  }

  /**
   * Get the divider to use when grouping by list
   * @param object
   * @returns {{objectDivider: string, textDisplay: string}}
   */
  getListDivider(object)
  {
    let listTitle = false;

    if (object.list && this.lists[object.list]) {
      listTitle = this.lists[object.list].title;
    }

    if (listTitle === false) {
      return {
        objectDivider: 'none',
        textDisplay: 'No List'
      }
    }

    return {
      objectDivider: listTitle,
      textDisplay: listTitle
    }
  }

  /**
   * Get the divider to use when grouping by creator
   * @param object
   * @returns {{objectDivider: string, textDisplay: string}}
   */
  getCreatorDivider(object)
  {
    let creator = false;

    if (object.creator && this.agents[object.creator]) {
      creator = this.agents[object.creator].name;
    }

    if (creator === false) {
      return {
        objectDivider: 'none',
        textDisplay: 'No Creator'
      }
    }

    return {
      objectDivider: creator,
      textDisplay: creator
    }
  }

  /**
   * Get the divider to use when grouping by done date
   * @param object
   * @returns {{objectDivider: string, textDisplay: string}}
   */
  getDoneDateDivider(object) {
    let objectDivider = false;
    let textDisplay = false;

    if (object.date_done) {
      switch (true) {
        case (Moment(object.date_done).isAfter(Moment().startOf('hour'))):
          objectDivider = 'hour';
          textDisplay = 'This Hour';
          break;
        case (Moment(object.date_done).isAfter(Moment().startOf('day'))):
          objectDivider = 'day';
          textDisplay = 'Today';
          break;
        case (Moment(object.date_done).isAfter(Moment().startOf('day').subtract(1, 'd'))):
          objectDivider = 'tomorrow';
          textDisplay = 'Yesterday';
          break;
        case (Moment(object.date_done).isAfter(Moment().startOf('week'))):
          objectDivider = 'week';
          textDisplay = 'This Week';
          break;
        case (Moment(object.date_done).isAfter(Moment().startOf('week').subtract(7, 'd'))):
          objectDivider = 'lastweek';
          textDisplay = 'Last Week';
          break;
        case (Moment(object.date_done).isAfter(Moment().startOf('month'))):
          objectDivider = 'month';
          textDisplay = 'This Month';
          break;
        case (Moment(object.date_done).isAfter(Moment().startOf('month').subtract(1, 'M'))):
          objectDivider = 'lastmonth';
          textDisplay = 'Last Month';
          break;
        case (Moment(object.date_done).isAfter(Moment().startOf('year'))):
          objectDivider = 'year';
          textDisplay = 'This Year';
          break;
        default:
          objectDivider = 'forever';
          textDisplay = 'Older';
          break;
      }
    }

    return {
      objectDivider: objectDivider,
      textDisplay: textDisplay
    }
  }

  /**
   * Get the divider to use when grouping by labels
   * @param object
   * @returns {{objectDivider: string, textDisplay: string}}
   */
  getLabelDivider(object)
  {
    let labelsDivider = false;

    if (object.labels && object.labels.length > 0) {
      let labels = object.labels;
      labels.sort((a, b) => {
        return a.toLowerCase().localeCompare(b.toLowerCase());
      });
      labelsDivider = labels.join(', ');
    }

    if (labelsDivider === false) {
      return {
        objectDivider: 'none',
        textDisplay: 'No Labels'
      }
    }

    return {
      objectDivider: labelsDivider,
      textDisplay: labelsDivider
    }
  }

  /**
   * Get the divider to use when grouping by linked ticket
   * @param object
   * @returns {{objectDivider: string, textDisplay: string}}
   */
  getTicketDivider(object)
  {
    let ticketDivider = false;
    if (object.linked_items && object.linked_items.length > 0) {
      object.linked_items.forEach((item) => {
        if (this.links[item] && this.links[item].ticket && ticketDivider === false) {
          ticketDivider = this.tickets[this.links[item].ticket].subject;
        }
      });
    }

    if (ticketDivider === false) {
      return {
        objectDivider: 'none',
        textDisplay: 'No Ticket'
      }
    }

    return {
      objectDivider: ticketDivider,
      textDisplay: ticketDivider
    }
  }
}