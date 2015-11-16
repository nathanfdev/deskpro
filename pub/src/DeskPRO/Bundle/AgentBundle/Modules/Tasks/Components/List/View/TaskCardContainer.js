import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { selectedSelector } from '../../../Selectors/list';
import { toggleSelected } from '../../../Actions/listActions';

@connect(state => ({
  selectedTasks: selectedSelector(state)
}))
export class TaskCardContainer extends React.Component {

  static propTypes = {
    selectedTasks: PropTypes.array.isRequired,
    task: PropTypes.object.isRequired,
    children: PropTypes.node.isRequired
  };

  onToggleSelected = () => {
    const { dispatch, task } = this.props;
    dispatch(toggleSelected(task.get('id')));
  };

  render() {
    const { selectedTasks, task, children } = this.props;
    const childProps = children.props;
    const selected = selectedTasks.indexOf(task.get('id')) !== -1;

    return React.cloneElement(children, {
      ...childProps,

      task: task,
      selected: selected,
      onToggleSelected: this.onToggleSelected
    });
  }
}
