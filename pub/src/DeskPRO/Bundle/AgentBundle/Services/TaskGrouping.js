import Moment from 'moment';

/**
 * @deprecated
 * @see Task/List/ListGroupContainer
 */
export default class TaskGrouping {
  constructor(projects = [], departments = [], teams = [], agents = [], lists = [], tickets = []) {
    this.projects = projects;
    this.departments = departments;
    this.teams = teams;
    this.agents = agents;
    this.lists = lists;
    this.tickets = tickets;

    this.futureDates = [
      { key: 'overdue', title: 'Overdue', value: false },
      { key: 'hour', title: 'This Hour', value: Moment().endOf('hour').utc().format() },
      { key: 'day', title: 'Today', value: Moment().endOf('day').utc().format() },
      { key: 'tomorrow', title: 'Tomorrow', value: Moment().endOf('day').add(1, 'd').utc().format() },
      { key: 'week', title: 'This Week', value: Moment().endOf('week').utc().format() },
      { key: 'nextweek', title: 'Next Week', value: Moment().endOf('week').add(7, 'd').utc().format() },
      { key: 'month', title: 'This Month', value: Moment().endOf('month').utc().format() },
      { key: 'nextmonth', title: 'Next Month', value: Moment().endOf('month').add(1, 'M').utc().format() },
      { key: 'year', title: 'This Year', value: Moment().endOf('year').utc().format() },
      { key: 'forever', title: 'Other', value: false }
    ];

    this.pastDates = [
      { key: 'hour', title: 'This Hour', value: Moment().startOf('hour').utc().format() },
      { key: 'day', title: 'Today', value: Moment().startOf('day').utc().format() },
      { key: 'tomorrow', title: 'Yesterday', value: Moment().startOf('day').subtract(1, 'd').utc().format() },
      { key: 'week', title: 'This Week', value: Moment().startOf('week').utc().format() },
      { key: 'lastweek', title: 'Last Week', value: Moment().startOf('week').subtract(7, 'd').utc().format() },
      { key: 'month', title: 'This Month', value: Moment().startOf('month').utc().format() },
      { key: 'lastmonth', title: 'Last Month', value: Moment().startOf('month').subtract(1, 'M').utc().format() },
      { key: 'year', title: 'This Year', value: Moment().startOf('year').utc().format() },
      { key: 'forever', title: 'Older', value: false }
    ];
  }

  /*
   * Get the divider to return according to the grouping type
   * @param object object
   * @param grouping string
   * @return string
   */
  getDivider(object, grouping = false) {
    let results = false;

    switch (grouping) {
      case 'due':
        results = this.getDueDivider(object);
        break;
      case 'created':
        results = this.getCreatedDivider(object);
        break;
      case 'assignee':
        results = this.getAssigneeDivider(object);
        break;
      case 'project':
        results = this.getProjectDivider(object);
        break;
      case 'list':
        results = this.getListDivider(object);
        break;
      case 'creator':
        results = this.getCreatorDivider(object);
        break;
      case 'done':
        results = this.getDoneDateDivider(object);
        break;
      case 'labels':
        results = this.getLabelDivider(object);
        break;
      case 'ticket':
        results = this.getTicketDivider(object);
        break;
      default:
        return false;
    }

    return results;
  }

  /*
   * Returns an ID for the column / group
   * @param object
   * @param grouping
   * @returns string
   */
  getGroup(object, grouping) {
    let result = false;

    switch (grouping) {
      case 'due':
        result = this.getDueDivider(object);
        break;
      case 'created':
        result = this.getCreatedDivider(object);
        break;
      case 'assignee':
        result = this.getAssigneeDivider(object);
        break;
      case 'project':
        result = object.get('project') ? 'project_' + object.get('project') : 'none';
        break;
      case 'list':
        result = object.get('list') ? 'list_' + object.get('list') : 'none';
        break;
      case 'creator':
        result = 'creator_' + object.get('creator');
        break;
      case 'done':
        result = this.getDoneDateDivider(object);
        break;
      case 'labels':
        result = this.getLabelDivider(object);
        break;
      case 'ticket':
        result = this.getTicketDivider(object);
        break;
      default:
        return false;
    }

    return result;
  }

  /*
   * Get the details of the groupings according to the type to group by
   * Returns and array of objects, each object containing ID, a title, a field to update, and a value to update with
   * @param type
   * @param direction
   * @returns {Array}
   */
  getRawGroupings(type, direction) {
    const returnGroups = [];
    const reverse = 'desc';
    switch (type) {
      case 'project':
        this.projects.forEach((project) => {
          returnGroups.push({
            id: project.get('id'),
            title: project.get('title'),
            updateField: 'project',
            updateValue: project.get('id'),
            key: 'project_' + project.get('id')
          });
        });
        returnGroups.sort((first, second) => {
          if (first.title === second.title) {
            return 0;
          }

          return first.title > second.title ? 1 : -1;
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
            updateField: null,
            updateValue: null,
            key: date.key
          });
        });
        break;
      case 'assignee':
        this.departments.forEach((department) => {
          returnGroups.push({
            id: 'department_' + department.get('id').toString(),
            title: department.get('title'),
            updateField: 'departments',
            updateValue: [department.get('id')],
            key: 'department_' + department.get('id')
          });
        });
        this.teams.forEach((team) => {
          returnGroups.push({
            id: 'team_' + team.get('id').toString(),
            title: team.get('name'),
            updateField: 'teams',
            updateValue: [team.get('id')],
            key: 'team_' + team.get('id')
          });
        });
        this.agents.forEach((agent) => {
          returnGroups.push({
            id: 'agent_' + agent.get('id').toString(),
            title: agent.get('name'),
            updateField: 'agents',
            updateValue: [agent.get('id')],
            key: 'agent_' + agent.get('id')
          });
        });

        returnGroups.unshift({
          id: 'none',
          title: 'Unassigned',
          updateField: 'agents',
          updateValue: false,
          key: 'none'
        });
        break;
      case 'list':
      default:
        this.lists.map((listObject) => {
          returnGroups.push({
            id: listObject.get('id'),
            title: listObject.get('title'),
            updateField: 'list',
            updateValue: listObject.get('id'),
            key: 'list_' + listObject.get('id')
          });
        });

        returnGroups.sort((first, second) => {
          if (first.display_order === second.display_order) {
            return 0;
          }

          return first.display_order > second.display_order ? 1 : -1;
        });

        returnGroups.push({
          id: 'none',
          title: 'Tasks not in any list',
          updateField: 'list',
          updateValue: false,
          key: 'none'
        });
        break;
    }

    if (reverse === direction) {
      returnGroups.reverse();
    }

    return returnGroups;
  }

  /*
   * Get the divider to use when grouping by due date
   * @param object
   * @return string
   */
  getDueDivider(object) {
    let objectDivider = false;

    const dueDate = object.get('date_due');

    switch (true) {
      case (Moment(dueDate).utc().isBefore()):
        objectDivider = 'overdue';
        break;
      case (Moment(dueDate).local().isBefore(Moment().endOf('hour').local())):
        objectDivider = 'hour';
        break;
      case (Moment(dueDate).local().isBefore(Moment().endOf('day').local())):
        objectDivider = 'day';
        break;
      case (Moment(dueDate).local().isBefore(Moment().endOf('day').add(1, 'd').local())):
        objectDivider = 'tomorrow';
        break;
      case (Moment(dueDate).local().isBefore(Moment().endOf('week').local())):
        objectDivider = 'week';
        break;
      case (Moment(dueDate).local().isBefore(Moment().endOf('week').add(7, 'd').local())):
        objectDivider = 'nextweek';
        break;
      case (Moment(dueDate).local().isBefore(Moment().endOf('month').local())):
        objectDivider = 'month';
        break;
      case (Moment(dueDate).local().isBefore(Moment().endOf('month').add(1, 'M').local())):
        objectDivider = 'nextmonth';
        break;
      case (Moment(dueDate).local().isBefore(Moment().endOf('year').local())):
        objectDivider = 'year';
        break;
      default:
        objectDivider = 'forever';
        break;
    }

    return objectDivider;
  }

  /*
   * Get divider to use when grouping by created date
   * @param object
   * @return string
   */
  getCreatedDivider(object) {
    let objectDivider = false;
    const createdDate = object.get('date_created');

    switch (true) {
      case (Moment(createdDate).utc().isAfter(Moment().startOf('hour').local())):
        objectDivider = 'hour';
        break;
      case (Moment(createdDate).local().isAfter(Moment().startOf('day').local())):
        objectDivider = 'day';
        break;
      case (Moment(createdDate).local().isAfter(Moment().startOf('day').subtract(1, 'd').local())):
        objectDivider = 'tomorrow';
        break;
      case (Moment(createdDate).local().isAfter(Moment().startOf('week').local())):
        objectDivider = 'week';
        break;
      case (Moment(createdDate).local().isAfter(Moment().startOf('week').subtract(7, 'd').local())):
        objectDivider = 'lastweek';
        break;
      case (Moment(createdDate).local().isAfter(Moment().startOf('month').local())):
        objectDivider = 'month';
        break;
      case (Moment(createdDate).local().isAfter(Moment().startOf('month').subtract(1, 'M').local())):
        objectDivider = 'lastmonth';
        break;
      case (Moment(createdDate).local().isAfter(Moment().startOf('year').local())):
        objectDivider = 'year';
        break;
      default:
        objectDivider = 'forever';
        break;
    }

    return objectDivider;
  }

  /*
   * Get divider to use when grouping by assignee
   * @param object
   * @return string
   */
  getAssigneeDivider(object) {
    let assignee = false;
    if (object.get('agents').length > 0) {
      assignee = 'agent_' + object.get('agents').get(0).toString();
    } else if (object.get('teams').length > 0) {
      assignee = 'team_' + object.get('teams').get(0).toString();
    } else if (object.get('departments').length > 0) {
      assignee = 'department_' + object.get('departments').get(0).toString();
    }

    if (assignee === false) {
      return 'none';
    }

    return assignee;
  }

  /*
   * Get divider to use when grouping by assignee
   * @param object
   * @return string
   */
  getProjectDivider(object) {
    if (object.get('project')) {
      return this.projects[object.get('project')].title;
    }

    return 'none';
  }

  /*
   * Get the divider to use when grouping by list
   * @param object
   * @return string
   */
  getListDivider(object) {
    if (object.get('list') && this.lists[object.get('list')]) {
      return this.lists[object.get('list')].title;
    }

    return 'none';
  }

  /*
   * Get the divider to use when grouping by creator
   * @param object
   * @return string
   */
  getCreatorDivider(object) {
    if (object.get('creator') && this.agents.get(object.get('creator'))) {
      return this.agents.get(object.get('creator')).get('name');
    }

    return 'none';
  }

  /*
   * Get the divider to use when grouping by done date
   * @param object
   * @return string
   */
  getDoneDateDivider(object) {
    let objectDivider = false;

    const doneDate = object.get('date_done');

    if (object.date_done) {
      switch (true) {
        case (Moment(doneDate).utc().isAfter(Moment().startOf('hour').local())):
          objectDivider = 'hour';
          break;
        case (Moment(doneDate).local().isAfter(Moment().startOf('day').local())):
          objectDivider = 'day';
          break;
        case (Moment(doneDate).local().isAfter(Moment().startOf('day').subtract(1, 'd').local())):
          objectDivider = 'tomorrow';
          break;
        case (Moment(doneDate).local().isAfter(Moment().startOf('week').local())):
          objectDivider = 'week';
          break;
        case (Moment(doneDate).local().isAfter(Moment().startOf('week').subtract(7, 'd').local())):
          objectDivider = 'lastweek';
          break;
        case (Moment(doneDate).local().isAfter(Moment().startOf('month').local())):
          objectDivider = 'month';
          break;
        case (Moment(doneDate).local().isAfter(Moment().startOf('month').subtract(1, 'M').local())):
          objectDivider = 'lastmonth';
          break;
        case (Moment(doneDate).local().isAfter(Moment().startOf('year').local())):
          objectDivider = 'year';
          break;
        default:
          objectDivider = 'forever';
          break;
      }
    }

    return objectDivider;
  }

  /*
   * Get the divider to use when grouping by labels
   * @param object
   * @return string
   */
  getLabelDivider(object) {
    if (object.get('labels') && object.get('labels').length > 0) {
      const labels = object.get('labels');
      labels.sort((first, second) => {
        return first.toLowerCase().localeCompare(second.toLowerCase());
      });
      return labels.join(', ');
    }

    return 'none';
  }

  /*
   * Get the divider to use when grouping by linked ticket
   *
   * @param object
   * @return string
   */
  getTicketDivider(object) {
    if (object.get('linked_tickets') && object.get('linked_tickets').length > 0) {
      let ticketDivider = false;
      object.get('linked_tickets').forEach((item) => {
        if (this.tickets.has(item)) {
          ticketDivider = this.tickets.get(item).get('subject');
        }
      });
      return ticketDivider;
    }

    return 'none';
  }
}
