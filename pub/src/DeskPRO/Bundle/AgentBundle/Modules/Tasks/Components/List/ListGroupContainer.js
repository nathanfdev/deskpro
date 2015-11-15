import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { currentSortSelector } from '../../Selectors/list';
import moment from 'moment';
import Immutable from 'immutable';

const dateGroups = [
  {title: 'This Hour', match: date => moment().isSame(date, 'hour')},
  {title: 'Yesterday', match: date => moment().subtract(1, 'day').isSame(date, 'day')},
  {title: 'Today', match: date => moment().isSame(date, 'day')},
  {title: 'Tomorrow', match: date => moment().add(1, 'day').isSame(date, 'day')},

  {title: 'Last Week', match: date => moment().subtract(1, 'week').isSame(date, 'week')},
  {title: 'This Week', match: date => moment().isSame(date, 'week')},
  {title: 'Next Week', match: date => moment().add('week').isSame(date, 'week')},

  {title: 'Last Month', match: date => moment().subtract(1, 'month').isSame(date, 'month')},
  {title: 'This Month', match: date => moment().isSame(date, 'month')},
  {title: 'Next Month', match: date => moment().add(1, 'month').isSame(date, 'month')},

  {title: 'Older', match: date => moment().isBefore(date, 'year')},
  {title: 'This Year', match: date => moment().isSame(date, 'year')},
  {title: 'Other', match: date => moment().isAfter(date, 'year')}
];

@connect(state => ({
  sort: currentSortSelector(state)
}))
export class ListGroupContainer extends React.Component {

  static propTypes = {
    tasks: PropTypes.object
  };

  getGroupKey(task) {
    const dateFilter = date => {
      const matched = dateGroups.filter(group => group.match(date)).shift();
      return matched ? matched.title : 'Other';
    };

    switch (this.props.sort) {
      case 'project':
        return task.get('project') ? ['project', task.get('project')] : 'None';
      case 'date_due':
        return dateFilter(task.get('date_due'));
      case 'date_done':
        return dateFilter(task.get('date_done'));
      case 'date_created':
        return dateFilter(task.get('date_created'));
      case 'assignee':
        if (task.get('agents').size) {
          return ['agent', task.get('agents').first()];
        } else if (task.get('teams').size) {
          return ['team', task.get('teams').first()];
        } else if (task.get('departments').size) {
          return ['department', task.get('departments').first()];
        }

        return 'None';
      case 'list':
      default:
        return task.get('list') ? ['list', task.get('list')] : 'Tasks not in any list';
    }
  }

  render() {
    const { tasks } = this.props;
    const child = this.props.children;
    const childProps = child.props;
    const taskGroups = {};

    tasks.forEach(task => {
      const groupTitle = this.getGroupKey(task);
      if (!taskGroups[groupTitle]) {
        taskGroups[groupTitle] = {
          title: groupTitle,
          elements: []
        };
      }

      taskGroups[groupTitle].elements.push(task);
    });

    return React.cloneElement(child, {
      ...childProps,
      tasks: tasks,
      taskGroups: Immutable.fromJS(taskGroups)
    });
  }
}
