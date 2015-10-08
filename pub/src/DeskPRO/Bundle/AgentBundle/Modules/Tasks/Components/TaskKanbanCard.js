import React from 'react';
import ReactDOM from 'react-dom';
import Moment from 'moment';
import { DragSource, DropTarget } from 'react-dnd';
import { IntlMixin, FormattedDate } from 'react-intl';
import DragTypes from '../../../Services/DragTypes.js';
import $ from 'jquery';
import { getEmptyImage } from 'react-dnd/modules/backends/HTML5';

const cardTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();
    if (item.id !== props.task.id) {
      props.moveCard(item, props.task);
    }
  }
};

const listCardSource = {
  beginDrag(props, monitor, component) {
    const width = $(ReactDOM.findDOMNode(component)).width();

    return {
      id: props.task.id,
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
    agents: React.PropTypes.array,
    teams: React.PropTypes.array,
    departments: React.PropTypes.array,
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

    if (this.props.task.get('agents') && this.props.task.get('agents').length > 0) {
      assigneeName = this.props.agents[this.props.task.get('agents')[0]].name;
    } else if (this.props.task.get('teams') && this.props.task.get('teams').length > 0) {
      assigneeName = this.props.teams[this.props.task.get('teams')[0]].name;
    } else if (this.props.task.get('departments') && this.props.task.get('departments').length > 0) {
      assigneeName = this.props.departments[this.props.task.get('departments')[0]].title;
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
