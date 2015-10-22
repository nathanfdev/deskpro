import React from 'react';
import ReactDOM from 'react-dom';
import Moment from 'moment';
import { DragSource, DropTarget } from 'react-dnd';
import DragTypes from '../../../Services/DragTypes.js';
import jQuery from 'jquery';
import { getEmptyImage } from 'react-dnd-html5-backend';

const cardTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();
    if (item.id !== props.task.get('id')) {
      props.moveCard(item, props.task);
    }
  }
};

const listCardSource = {
  beginDrag(props, monitor, component) {
    const width = jQuery(ReactDOM.findDOMNode(component)).width();

    return {
      id: props.task.get('id'),
      details: props.task,
      dispatch: props.dispatch,
      source: props.source,
      departments: props.departments,
      teams: props.teams,
      agents: props.agents,
      projects: props.projects,
      tickets: props.tickets,
      width: width,
      subtype: 'kanban'
    };
  }
};

const TaskKanbanCard = React.createClass({
  propTypes: {
    updateMassActions: React.PropTypes.func,
    task: React.PropTypes.object,
    agents: React.PropTypes.object,
    teams: React.PropTypes.object,
    departments: React.PropTypes.object,
    connectDragPreview: React.PropTypes.func,
    connectDragSource: React.PropTypes.func,
    connectDropTarget: React.PropTypes.func,
    isOver: React.PropTypes.bool,
    selected: React.PropTypes.bool,
    order: React.PropTypes.string
  },

  componentDidMount: function() {
    this.props.connectDragPreview(getEmptyImage(), {
      // IE fallback: specify that we'd rather screenshot the node
      // when it already knows it's being dragged so we can hide it with CSS.
      captureDraggingState: true
    });
  },

  toggleMassAction: function() {
    this.props.updateMassActions(this.props.task.get('id'));
  },

  render: function() {
    let assigneeName = '';

    if (this.props.task.get('agents') && this.props.task.get('agents').size > 0) {
      assigneeName = this.props.agents.get(this.props.task.get('agents').get(0)).get('name');
    } else if (this.props.task.get('teams') && this.props.task.get('teams').size > 0) {
      assigneeName = this.props.teams.get(this.props.task.get('teams').get(0)).get('name');
    } else if (this.props.task.get('departments') && this.props.task.get('departments').size > 0) {
      assigneeName = this.props.departments.get(this.props.task.get('departments').get(0)).get('title');
    }

    const placeHolder = this.props.isOver ? 'placeholder is-over' : 'placeholder';

    const selected = this.props.selected;

    const result = (<div>
          <div className="card task-card">
            <div className="card-status-bar status-bar-left" />
            <div className="card-status-bar status-bar-right" />

            <div className="card-checkbox">
              <span className="checkbox" onClick={this.toggleMassAction}>
                {selected ? <i className="fa fa-check" /> : '' }
              </span>
            </div>

            <div className="content">
              <h1 className={this.props.task.get('is_done') ? 'complete' : ''}>{this.props.task.get('title')}</h1>
              <div className="card-line task-details">
                <div className="top-right-box">
                  <span className="assignment">
                    {assigneeName}
                  </span>
                </div>
                <div>
                  <i className="fa fa-calendar-o" /> Due: {this.props.task.get('date_due') ? Moment(this.props.task.get('date_due')).local().format('MMMM D, YYYY')
                  : 'N/A' }
                </div>
              </div>
              <hr/>
              <div className="card-line task-properties">
                <span>{this.props.task.get('comment_count', 0)} <i className="fa fa-comment"/></span>

                {this.props.task.get('subtasks_total', 0) > 0 ?
                  <span>
                    <span className="disc"/>
                    <div className="subtask-count">{this.props.task.get('subtasks_done')}/{this.props.task.get('subtasks_total')} <i className="fa fa-folder-open"/></div>
                  </span>
                : ''}
              </div>
            </div>
          </div>
          <div className={placeHolder} />
        </div>);

    if (this.props.order === 'list') {
      return this.props.connectDragSource(this.props.connectDropTarget(result));
    }

    return this.props.connectDragSource(result);
  }
});

module.exports = DragSource(DragTypes.TASK, listCardSource, (connect, monitor) => ({
  connectDragSource: connect.dragSource(),
  connectDragPreview: connect.dragPreview(),
  isDragging: monitor.isDragging()
}))(DropTarget(DragTypes.TASK, cardTarget, (connect, monitor) => ({
  connectDropTarget: connect.dropTarget(),
  isOver: monitor.isOver()
}))(TaskKanbanCard));
