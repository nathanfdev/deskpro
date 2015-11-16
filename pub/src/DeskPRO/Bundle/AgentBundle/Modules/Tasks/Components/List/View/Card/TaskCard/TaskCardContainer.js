import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { TaskCard } from './TaskCard';
import { selectedSelector } from '../../../../../Selectors/list';

@connect(state => ({
  selectedTasks: selectedSelector(state)
}))
export class TaskCardContainer extends React.Component {

  static propTypes = {
    selectedTasks: PropTypes.array.isRequired,
    task: PropTypes.object.isRequired
  };

  render() {
    const { selectedTasks, task } = this.props;
    const selected = selectedTasks.indexOf(task.get('id')) !== -1;

    return <TaskCard selected={selected} {...this.props} />;
  }
}
