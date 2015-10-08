import Moment from 'moment';

export default class TaskGrouping {
  constructor(projects = [], departments = [], teams = [], agents = [], lists = [], links = [], tickets = []) {
    this.projects = projects;
    this.departments = departments;
    this.teams = teams;
    this.agents = agents;
    this.lists = lists;
    this.links = links;
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

  /**
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

  /**
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

  /**
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

        returnGroups.sort((a, b) => {
          if (a.display_order === b.display_order) {
            return 0;
          }

          return a.display_order > b.display_order ? 1 : -1;
        });

        returnGroups.push({
          id: 'none',
          title: 'Tasks not in any list',
          updateField: 'list',
          updateValue: false,
          key: 'none'
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
        returnGroups.sort((a, b) => {
          if (a.title === b.title) {
            return 0;
          }

          return a.title > b.title ? 1 : -1;
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
            id: 'department_' + department.id.toString(),
            title: department.title,
            updateField: 'departments',
            updateValue: [department.id],
            key: 'department_' + department.id
          })
        });
        this.teams.forEach((team) => {
          returnGroups.push({
            id: 'team_' + team.id.toString(),
            title: team.name,
            updateField: 'teams',
            updateValue: [team.id],
            key: 'team_' + team.id
          })
        });
        this.agents.forEach((agent) => {
          returnGroups.push({
            id: 'agent_' + agent.id.toString(),
            title: agent.name,
            updateField: 'agents',
            updateValue: [agent.id],
            key: 'agent_' + agent.id
          })
        });

        returnGroups.unshift({
          id: 'none',
          title: 'Unassigned',
          updateField: 'agents',
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

  /**
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

  /**
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

  /**
   * Get divider to use when grouping by assignee
   * @param object
   * @return string
   */
  getAssigneeDivider(object) {
    let assignee = false;
    if (object.get('agents').length > 0) {
      assignee = 'agent_' + object.get('agents')[0].toString();
    } else if (object.get('teams').length > 0) {
      assignee = 'team_' + object.get('teams')[0].toString();
    } else if (object.get('departments').length > 0) {
      assignee = 'department_' + object.get('departments')[0].toString();
    }

    if (assignee === false) {
      return 'none';
    }

    return assignee;
  }

  /**
   * Get divider to use when grouping by assignee
   * @param object
   * @return string
   */
  getProjectDivider(object) {
    let project = false;
    if (object.get('project')) {
      project = this.projects[object.get('project')].title;
    }

    if (project === false) {
      return 'none';
    }

    return project;
  }

  /**
   * Get the divider to use when grouping by list
   * @param object
   * @return string
   */
  getListDivider(object) {
    let listTitle = false;

    if (object.get('list') && this.lists[object.get('list')]) {
      listTitle = this.lists[object.get('list')].title;
    }

    if (listTitle === false) {
      return 'none';
    }

    return listTitle;
  }

  /**
   * Get the divider to use when grouping by creator
   * @param object
   * @return string
   */
  getCreatorDivider(object) {
    let creator = false;

    if (object.get('creator') && this.agents[object.get('creator')]) {
      creator = this.agents[object.get('creator')].name;
    }

    if (creator === false) {
      return 'none';
    }

    return creator;
  }

  /**
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

  /**
   * Get the divider to use when grouping by labels
   * @param object
   * @return string
   */
  getLabelDivider(object) {
    let labelsDivider = false;

    if (object.get('labels') && object.get('labels').length > 0) {
      const labels = object.get('labels');
      labels.sort((first, second) => {
        return first.toLowerCase().localeCompare(second.toLowerCase());
      });
      labelsDivider = labels.join(', ');
    }

    if (labelsDivider === false) {
      return 'none';
    }

    return labelsDivider;
  }

  /**
   * Get the divider to use when grouping by linked ticket
   * @param object
   * @return string
   */
  getTicketDivider(object) {
    let ticketDivider = false;
    if (object.get('linked_items') && object.get('linked_items').length > 0) {
      object.get('linked_items').forEach((item) => {
        if (this.links[item] && this.links[item].ticket && ticketDivider === false) {
          ticketDivider = this.tickets[this.links[item].ticket].subject;
        }
      });
    }

    if (ticketDivider === false) {
      return 'none';
    }

    return ticketDivider;
  }
}
