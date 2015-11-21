import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import { toggleSelected } from '../../../Actions/listActions';
import { editTask } from '../../../Actions/listActions';
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

  onToggleDone = () => {
    const { task, dispatch } = this.props;
    const params = {
      is_done: !task.get('is_done')
    };

    dispatch(editTask(task.get('id'), params));
  };

  onChangeTitle = value => {
    const { task, dispatch } = this.props;
    const params = {
      title: value
    };

    dispatch(editTask(task.get('id'), params));
  };

  onChangeDate = value => {
    const { task, dispatch } = this.props;
    const params = {
      date_due: value
    };

    dispatch(editTask(task.get('id'), params));
  };

  onChangeDisplayOrder = taskId => {
    const { task, dispatch } = this.props;
    const params = {
      display_order: task.get('display_order')
    };

    dispatch(editTask(taskId, params));
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
      onToggleSelected: this.onToggleSelected,
      onToggleDone: this.onToggleDone,
      onChangeDisplayOrder: this.onChangeDisplayOrder,
      onChangeTitle: this.onChangeTitle,
      onChangeDate: this.onChangeDate
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
  drop({ onChangeDisplayOrder }, monitor) {
    const item = monitor.getItem();
    onChangeDisplayOrder(item.id);
  }
};

export const groupTargetSpec = {
  drop({ updateData, onChangeGroup }, monitor) {
    const item = monitor.getItem();
    onChangeGroup(item.id, updateData);
  }
};

export const targetCollect = (dragConnect, monitor) => ({
  connectDropTarget: dragConnect.dropTarget(),
  isOver: monitor.isOver()
});
