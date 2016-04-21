import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import { toggleSelectedAction } from '../../../../Application/Actions/massActions';
import { editTask } from '../../../Actions/listActions';
import { selectedSelector } from '../../../../Application/Selectors/massActions';
import Immutable from 'immutable';
import {
  cardVisibleFieldsSelector,
  tableVisibleFieldsSelector,
  kanbanVisibleFieldsSelector,
  calendarVisibleFieldsSelector,
  currentOrderBySelector
} from '../../../Selectors/list';
import jQuery from 'jquery';

@connect(state => ({
  selectedTasks: selectedSelector(state),
  cardVisibleFields: cardVisibleFieldsSelector(state),
  tableVisibleFields: tableVisibleFieldsSelector(state),
  kanbanVisibleFields: kanbanVisibleFieldsSelector(state),
  calendarVisibleFields: calendarVisibleFieldsSelector(state),
  currentOrderBy: currentOrderBySelector(state)
}))

export class TaskCardEditContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    selectedTasks: PropTypes.object.isRequired,
    task: PropTypes.object.isRequired,
    children: PropTypes.node.isRequired,
    onUpdate: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      editing: false,
      selected: props.selectedTasks.indexOf(props.task.get('id')) !== -1,
      task: props.task
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      selected: props.selectedTasks.indexOf(props.task.get('id')) !== -1,
      task: props.task
    });
  }

  shouldComponentUpdate(props, state) {
    if (state.selected !== this.state.selected) {
      return true;
    }

    return !Immutable.is(this.state.task, state.task);
  }

  componentWillUpdate(props, state) {
    this.props.onUpdate && this.props.onUpdate(state.task);
  }

  onToggleSelected = () => {
    const { dispatch } = this.props;
    const { task } = this.state;
    this.setState({ selected: !this.state.selected });
    dispatch(toggleSelectedAction(task.get('id')));
  };

  onChange = (prop, value) => {
    const { dispatch } = this.props;
    let { task } = this.state;
    let params;

    if (prop === 'assignee') {
      const agents = value.get('agent') ? [value.get('agent')] : [];
      const teams = value.get('team') ? [value.get('team')] : [];
      const departments = value.get('department') ? [value.get('department')] : [];
      params = {
        agents: agents,
        teams: teams,
        departments: departments
      };
      task = task.mergeWith(value);
    } else if ('linked_items' === prop) {

      task = task.withMutations(map => {
        map
          .set('linked_tickets', value.get('linked_tickets'))
          .set('linked_articles', value.get('linked_articles'))
          .set('linked_chats', value.get('linked_chats'));
      });

      params = {
        linked_tickets: value.get('linked_tickets').toArray(),
        linked_articles: value.get('linked_articles').toArray(),
        linked_chats: value.get('linked_chats').toArray()
      };

    } else {
      params = { [prop]: value };
      task = task.set(prop, value);
    }

    this.setState({ task: task });
    dispatch(editTask(task.get('id'), params));
  };

  render() {
    const props = this.props;
    const { children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,
      ...props,

      task: this.state.task,
      selected: this.state.selected,
      onToggleSelected: this.onToggleSelected,
      onChange: this.onChange
    });
  }
}

export const cardSourceSpec = {
  beginDrag({ task }, {}, component) {
    return {
      id: task.get('id'),
      width: jQuery(ReactDOM.findDOMNode(component)).width()
    };
  },
  canDrag({ editing, updateData = {} }) {
    return !editing && !updateData.date_created && !updateData.date_done;
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
