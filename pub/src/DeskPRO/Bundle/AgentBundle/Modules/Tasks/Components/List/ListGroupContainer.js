import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { currentSortSelector } from '../../Selectors/list';
import moment from 'moment';

const dateGroups = {
  past: {
    hour: {title: 'This Hour', value: moment().startOf('hour')},
    day: {title: 'Today', value: moment().startOf('day')},
    tomorrow: {title: 'Yesterday', value: moment().startOf('day').subtract(1, 'd')},
    week: {title: 'This Week', value: moment().startOf('week')},
    lastWeek: {title: 'Last Week', value: moment().startOf('week').subtract(7, 'd')},
    month: {title: 'This Month', value: moment().startOf('month')},
    lastMonth: {title: 'Last Month', value: moment().startOf('month').subtract(1, 'M')},
    year: {title: 'This Year', value: moment().startOf('year')},
    forever: {title: 'Older', value: false}
  },
  future: {
    overdue: {title: 'Overdue', value: false},
    hour: { title: 'This Hour', value: moment().endOf('hour')},
    day: {title: 'Today', value: moment().endOf('day')},
    tomorrow: {title: 'Tomorrow', value: moment().endOf('day').add(1, 'd')},
    week: {title: 'This Week', value: moment().endOf('week')},
    nextWeek: {title: 'Next Week', value: moment().endOf('week').add(7, 'd')},
    month: {title: 'This Month', value: moment().endOf('month')},
    nextMonth: {title: 'Next Month', value: moment().endOf('month').add(1, 'M')},
    year: {title: 'This Year', value: moment().endOf('year')},
    forever: {title: 'Other', value: false}
  }
};

@connect(state => ({
  sort: currentSortSelector(state)
}))
export class ListGroupContainer extends React.Component {

  static propTypes = {
    tasks: PropTypes.object
  };

  render() {
    const child = this.props.children;
    const childProps = child.props;
    const taskGroups = {};

    return React.cloneElement(child, {
      ...childProps,
      tasks: this.props.tasks,
      taskGroups: taskGroups
    });
  }
}
