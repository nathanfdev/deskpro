import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { toggleSelected } from '../../../Actions/listActions';
import {
  selectedSelector,
  cardVisibleFieldsSelector,
  tableVisibleFieldsSelector,
  kanbanVisibleFieldsSelector,
  calendarVisibleFieldsSelector
} from '../../../Selectors/list';

@connect(state => ({
  selectedTasks: selectedSelector(state),
  cardVisibleFields: cardVisibleFieldsSelector(state),
  tableVisibleFields: tableVisibleFieldsSelector(state),
  kanbanVisibleFields: kanbanVisibleFieldsSelector(state),
  calendarVisibleFields: calendarVisibleFieldsSelector(state)
}))
export class TaskCardContainer extends React.Component {

  static propTypes = {
    selectedTasks: PropTypes.object.isRequired,
    task: PropTypes.object.isRequired,
    children: PropTypes.node.isRequired
  };

  onToggleSelected = () => {
    const { dispatch, task } = this.props;
    dispatch(toggleSelected(task.get('id')));
  };

  render() {
    const props = this.props;
    const { selectedTasks, task, children } = props;
    const childProps = children.props;
    const selected = selectedTasks.indexOf(task.get('id')) !== -1;

    return React.cloneElement(children, {
      ...childProps,
      ...props,

      selected: selected,
      onToggleSelected: this.onToggleSelected
    });
  }
}
