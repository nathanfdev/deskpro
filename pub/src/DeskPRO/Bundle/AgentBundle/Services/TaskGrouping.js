import Moment from "moment";

export default class TaskGrouping {
  constructor(projects = {}, departments = {}, teams = {}, agents = {}) {
    this.projects = projects;
    this.departments = departments;
    this.teams = teams;
    this.agents = agents;
    this.lastGrouping = '';
  }

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
      default:
        return false;
    }

    return results;
  }

  getDueDividers(object)
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

  getCreatedDividers(object)
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

  getAssigneeDividers(object)
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

  getProjectDividers(object)
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
}