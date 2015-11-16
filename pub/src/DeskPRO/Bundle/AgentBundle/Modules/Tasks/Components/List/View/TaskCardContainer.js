import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { selectedSelector } from '../../../Selectors/list';

@connect(state => ({
  selectedTasks: selectedSelector(state)
}))
export class TaskCardContainer extends React.Component {

  static propTypes = {
    selectedTasks: PropTypes.array.isRequired,
    task: PropTypes.object.isRequired
  };

  render() {
    const { selectedTasks, task, dispatch } = this.props;
    const child = this.props.children;
    const childProps = child.props;
    const selected = selectedTasks.indexOf(task.get('id')) !== -1;

    return React.cloneElement(child, {
      ...childProps,

      task: task,
      selected: selected,
      dispatch: dispatch
    });
  }
}
