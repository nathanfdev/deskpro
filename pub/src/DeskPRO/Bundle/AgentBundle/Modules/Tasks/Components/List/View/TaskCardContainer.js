import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import { toggleSelected } from '../../../Actions/listActions';
import {
  selectedSelector,
  cardVisibleFieldsSelector,
  tableVisibleFieldsSelector,
  kanbanVisibleFieldsSelector,
  calendarVisibleFieldsSelector,
  currentSortSelector
} from '../../../Selectors/list';
import jQuery from 'jquery';

@connect(state => ({
  selectedTasks: selectedSelector(state),
  cardVisibleFields: cardVisibleFieldsSelector(state),
  tableVisibleFields: tableVisibleFieldsSelector(state),
  kanbanVisibleFields: kanbanVisibleFieldsSelector(state),
  calendarVisibleFields: calendarVisibleFieldsSelector(state),
  currentSort: currentSortSelector(state)
}))
export class TaskCardContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
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

export const cardSourceSpec = {
  beginDrag({ task }, monitor, component) {
    return {
      id: task.get('id'),
      width: jQuery(ReactDOM.findDOMNode(component)).width()
    };
  }
};

export const cardSourceCollect = (dragConnect, monitor) => ({
  connectDragSource: dragConnect.dragSource(),
  connectDragPreview: dragConnect.dragPreview(),
  isDragging: monitor.isDragging()
});

export const cardTargetSpec = {
  drop({ task }, monitor) {
    const item = monitor.getItem();
    console.log('edit task', task, item.id);
  }
};

export const groupTargetSpec = {
  drop({ param, value }, monitor) {
    const item = monitor.getItem();
    console.log('edit task', param, value, item.id);
  }
};

export const targetCollect = (dragConnect, monitor) => ({
  connectDropTarget: dragConnect.dropTarget(),
  isOver: monitor.isOver()
});
