import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import { toggleSelectedAction } from '../../../../Application/Actions/massActions';
import { editTask } from '../../../Actions/listActions';
import { selectedSelector } from '../../../../Application/Selectors/massActions';
import Immutable from 'immutable';
import { addToCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import {
  cardFieldsSelector,
  tableFieldsSelector,
  kanbanFieldsSelector,
  calendarFieldsSelector,
  currentOrderBySelector
} from '../../../Selectors/list';
import jQuery from 'jquery';

@connect(state => ({
  selectedTasks:  selectedSelector(state),
  cardFields:     cardFieldsSelector(state),
  tableFields:    tableFieldsSelector(state),
  kanbanFields:   kanbanFieldsSelector(state),
  calendarFields: calendarFieldsSelector(state),
  currentOrderBy: currentOrderBySelector(state)
}))

export class TaskCardEditContainer extends React.Component {

  static propTypes = {
    dispatch:      PropTypes.func.isRequired,
    selectedTasks: PropTypes.object.isRequired,
    task:          PropTypes.object.isRequired,
    children:      PropTypes.node.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      editing:  false,
      selected: props.selectedTasks.indexOf(props.task.get('id')) !== -1,
      task:     props.task
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      selected: props.selectedTasks.indexOf(props.task.get('id')) !== -1,
      task:     props.task
    });
  }

  shouldComponentUpdate(props, state) {
    if (state.selected !== this.state.selected) {
      return true;
    }

    return !Immutable.is(this.state.task, state.task)
      || !Immutable.is(props.cardFields, this.props.cardFields)
      || !Immutable.is(props.tableFields, this.props.tableFields)
      || !Immutable.is(props.kanbanFields, this.props.kanbanFields)
      || !Immutable.is(props.calendarFields, this.props.calendarFields)
      || !Immutable.is(props.currentOrderBy, this.props.currentOrderBy)
      ;
  }

  componentWillUpdate(props, state) {
    if (this.props.onUpdate) {
      this.props.onUpdate(state.task);
    }
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
      const agents = value.get('agents').toArray();
      const teams = value.get('teams').toArray();
      const departments = value.get('departments').toArray();
      params = {
        agents,
        teams,
        departments
      };
      task = task.mergeWith(value);
    } else if (prop === 'linked_items') {
      task = task.withMutations(map => {
        map.set('linked_tickets', value.get('linked_tickets')).set('linked_articles', value.get('linked_articles')).
          set('linked_chats', value.get('linked_chats'));
      });

      params = {
        linked_tickets:  value.get('linked_tickets').toArray(),
        linked_articles: value.get('linked_articles').toArray(),
        linked_chats:    value.get('linked_chats').toArray()
      };
    } else {
      params = { [prop]: value };
      task = task.set(prop, value);
    }

    this.setState({ task });
    dispatch(addToCollection('Task', 'all', Immutable.List([task])));
    dispatch(editTask(task.get('id'), params));
  };

  render() {
    const props = this.props;
    const { children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,
      ...props,

      task:             this.state.task,
      selected:         this.state.selected,
      onToggleSelected: this.onToggleSelected,
      onChange:         this.onChange
    });
  }
}

export const cardSourceSpec = {
  beginDrag({ task }, {}, component) {
    return {
      id:    task.get('id'),
      width: jQuery(ReactDOM.findDOMNode(component)).width()
    };
  },
  canDrag({ editing, updateData = Immutable.Map() }) {
    return !editing && !updateData.get('date_created') && !updateData.get('date_done');
  }
};

export const cardSourceCollect = (dragConnect, monitor) => ({
  connectDragSource:  dragConnect.dragSource(),
  connectDragPreview: dragConnect.dragPreview(),
  isDragging:         monitor.isDragging()
});

export const cardTargetSpec = {
  drop({ onChangeDisplayOrder }, monitor) {
    const item = monitor.getItem();
    onChangeDisplayOrder(item.id);
  }
};

export const groupTargetSpec = {
  drop({ updateData, onChangeGroup }, monitor) {
    if (!updateData) return;
    const item = monitor.getItem();
    onChangeGroup(item.id, updateData);
  }
};

export const targetCollect = (dragConnect, monitor) => ({
  connectDropTarget: dragConnect.dropTarget(),
  isOver:            monitor.isOver()
});
