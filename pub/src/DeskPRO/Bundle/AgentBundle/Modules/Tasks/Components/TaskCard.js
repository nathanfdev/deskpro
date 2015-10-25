import React from 'react';
import ReactDOM from 'react-dom';
import { DragSource, DropTarget } from 'react-dnd';
import jQuery from 'jquery';
import Formsy from 'formsy-react';
import FRC from 'DeskPRO/Component/FormComponents/main.js';
import DragTypes from '../../../Services/DragTypes.js';
import Picker from 'anytime';
import Moment from 'moment';
import { getEmptyImage } from 'react-dnd-html5-backend';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { AgentTeamAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/AgentTeamAvatar';
import { DepartmentAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/DepartmentAvatar';

import { Card } from '../../Common/Components/ListFrame/Card';

const cardTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();
    if (item.details.get('id') !== props.task.get('id')) {
      props.moveCard(item, props.task);
    }
  }
};

const cardSource = {
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
      subtype: 'list'
    };
  }
};

const TaskCard = React.createClass({
  mixins: [
    require('react-onclickoutside')
  ],

  propTypes: {
    agents: React.PropTypes.object,
    connectDragPreview: React.PropTypes.func,
    connectDragSource: React.PropTypes.func,
    connectDropTarget: React.PropTypes.func,
    departments: React.PropTypes.object,
    editTask: React.PropTypes.func,
    isOver: React.PropTypes.bool,
    linked_items: React.PropTypes.object,
    order: React.PropTypes.string,
    projects: React.PropTypes.object,
    selected: React.PropTypes.bool,
    source: React.PropTypes.string,
    task: React.PropTypes.object,
    teams: React.PropTypes.object,
    tickets: React.PropTypes.object,
    toggleAssignWindow: React.PropTypes.func,
    toggleDone: React.PropTypes.func,
    updateMassActions: React.PropTypes.func
  },

  handleClickOutside: function() {
    if (this.state.editing === true) {
      this.setState({
        editing: false
      });

      const task = this.state.task;
      task.taskId = this.props.task.get('id');

      this.props.editTask(this.props.source, task);
    }
  },

  getInitialState: function() {
    return {
      expanded: false,
      editing: false,
      task: {}
    };
  },

  toggleDetails: function(event) {
    this.setState({
      expanded: !this.state.expanded
    });
    event.preventDefault();
  },

  editMode: function() {
    this.setState({
      editing: true
    });
  },

  handleTitleChange: function(name, value) {
    const task = this.state.task;
    task.title = value;
    this.setState({
      task: task
    });
  },

  toggleMassAction: function() {
    this.props.updateMassActions(this.props.task.get('id'));
  },

  componentDidMount: function() {
    const dueField = 'due-' + this.props.task.get('id');

    // Check if the due field actually exists before we try and add a date picker (e.g. on done tasks)
    if (typeof (this.refs[dueField]) !== 'undefined') {
      const dueButton = 'due-button-' + this.props.task.get('id');

      // Get the date for the task, and format it nicely
      const initial = this.props.task.date_due ? Moment(this.props.task.date_due).format() : null;

      // Create the picker
      const picker = new Picker({
        input: ReactDOM.findDOMNode(this.refs[dueField]),
        button: ReactDOM.findDOMNode(this.refs[dueButton]),
        initialValue: initial,
        format: 'hh:mm, MMMM D, YYYY'
      });
      picker.render();

      // Change the component state and submit the edit when the date is changed
      picker.on('change', (newDate) => {
        const task = this.state.task;
        task.date_due = newDate ? Moment(newDate).format() : null;
        this.setState({
          task: task
        });

        picker.updateInput();
        task.taskId = this.props.task.get('id');

        this.props.editTask(this.props.source, task);
      });
    }

    this.props.connectDragPreview(getEmptyImage(), {
      // IE fallback: specify that we'd rather screenshot the node
      // when it already knows it's being dragged so we can hide it with CSS.
      captureDraggingState: true
    });
  },

  getStyles: function(props) {
    const { isDragging } = props;

    return {
      // IE fallback: hide the real node using CSS when dragging
      // because IE will ignore our custom "empty image" drag preview.
      opacity: isDragging ? 0 : 1,
      height: isDragging ? 0 : ''
    };
  },

  dueIndicator: function(due) {
    const dueMoment = new Moment(due);

    let result = '';

    if (dueMoment.isSame(new Moment(), 'day')) {
      result = 'Today, ';
    } else if (dueMoment.isSame(new Moment().subtract(1, 'days'))) {
      result = 'Yesterday, ';
    } else {
      result = dueMoment.format('MMM Do YYYY, ');
    }

    result += dueMoment.format('hh:mm a');

    return result;
  },

  render: function() {
    const { task,
            projects,
            tickets,
            departments,
            teams,
            agents,
            source,
            connectDragSource,
            connectDropTarget
          } = this.props;

    const selected = this.props.selected;

    const detailsButtonText = this.state.expanded ? 'Collapse' : 'Expand';

    let ticketLink = undefined;
    let ticketTitle = 'Linked ticket';

    if (task.has('linked_tickets') && task.get('linked_tickets').size > 0) {
      task.get('linked_tickets').forEach((item) => {
        if (typeof tickets.get(item) !== 'undefined' && tickets.get(item) !== null) {
          ticketLink = '#' + item;
          ticketTitle = tickets.get(item).get('subject');
        }
      });
    }

    let assignee = null;
    let assigneeAvatar = null;

    if (task.has('agents') && task.get('agents').size > 0) {
      // We assume one assignment for now, though we will need to support more later
      const agentId = task.get('agents').first();
      assignee = agents.get(agentId);
      assigneeAvatar = (<PersonAvatar person={assignee} size="16" />);
    } else if (task.has('teams') && task.get('teams').size > 0) {
      const teamId = task.get('teams').first();
      assignee = teams.get(teamId);
      assigneeAvatar = (<AgentTeamAvatar agentTeam={assignee} size="16" />);
    } else if (task.has('departments') && task.get('departments').size > 0) {
      const departmentId = task.get('departments').first();
      assignee = departments.get(departmentId);
      assigneeAvatar = (<DepartmentAvatar department={assignee} size="16" />);
    }

    const titleClass = task.get('is_done', false) ? 'dpwd--card-title strikethrough' : 'dpwd--card-title';

    const dueField = 'due-' + task.get('id');
    const dueButton = 'due-button-' + task.get('id');

    const overdue = Moment(task.get('date_due')).isBefore();

    const placeHolder = this.props.isOver ? 'placeholder is-over' : 'placeholder';

    const result = (<div key={task.get('id')}>
      <Card minimized={task.get('is_done', false)} type="task">
        {
          task.get('is_done', false) ?
          <div className="dpw--single-card-mark-done dpw--single-card-mark-done-minimized"
               onClick={this.props.toggleDone.bind(this, task, source)}>
            <span>Done</span>
            <i className="fa fa-check"/>
          </div>
            :
          <div className="dpw--single-card-mark-done" onClick={this.props.toggleDone.bind(this, task, source)}>
            <i className="fa fa-check"/>
            <span>Mark Done</span>
          </div>
        }

        <div className="dpm--card-checkbox" onClick={this.toggleMassAction}>
          {selected ? <i className="fa fa-check"/> : '' }
        </div>

        <div className="dpw--card-line">
          <div className="dpw--card-line-left card-title">
            <div className={titleClass}>
              { !this.state.editing ?
                <h1 onDoubleClick={this.editMode}>{task.get('title')}</h1> :
                <Formsy.Form className="inline-form">
                  <h1><FRC.Input type="text" name="title" value={task.get('title')} onChange={this.handleTitleChange}/></h1>
                </Formsy.Form>
              }
            </div>
          </div>

          <div className="dpw--card-line-right">
            {task.get('is_done', false) ?
              <div className="dpw--card-expand">
                <a href="#" onClick={this.toggleDetails}>{detailsButtonText} <i className="fa fa-navicon"/></a>
              </div>
              :
              assignee ?
              <div className="dpwd--card-assigned" onClick={this.props.toggleAssignWindow.bind(this, task)}>
                <div className="dpw--avatar-face" style={{position: 'relative'}}>{assigneeAvatar}</div>
              </div> : '' }
          </div>
        </div>

        {!task.get('is_done', false) || this.state.expanded ?
          <div className="dpw--card-line">
            <div className="dpw--card-line-left">
              <span className={overdue ? 'overdue dpwd--card-line-item' : 'dpwd--card-line-item'} ref={dueButton}>
                <i className="fa fa-calendar-o"/> Due: {task.get('date_due') ? this.dueIndicator(task.get('date_due')) : 'N/A'}
                <input type="text" name="due-date" className="due-date-field" ref={dueField} disabled="disabled"/>
              </span>

              {task.get('project') && projects.has(task.get('project')) ? <span>
                <span className="dpw--card-disc"/>
                <span className="dpwd--card-line-item">
                  <i className="fa fa-book"/> {projects.get(task.get('project')).get('title')}
                </span>
              </span>
               : ''}

              {ticketLink ? <span>
                <span className="dpw--card-disc"/>

                <span className="dpwd--card-line-item">
                  <i className="fa fa-link"/> <a href={ticketLink}>{ticketTitle}</a>
                </span>
              </span> : ''}
           </div>

            <div className="dpw--card-line-right">
              <span className="dpwd--card-line-item">
                {task.get('comment_count', 0)} <i className="fa fa-comment"/>
              </span>

              {task.get('subtasks_total', 0) > 0 ?
                <span className="dpwd--card-line-item">
                  <div><span className="dpw--card-disc"/> {task.get('subtasks_done', 0)}/{task.get('subtasks_total', 0)} <i
                    className="fa fa-folder-open"/></div>
                </span>
               : ''}
            </div>
          </div>
          : '' }
      </Card>

      <div className={placeHolder}/>
    </div>);

    if (this.props.order === 'list') {
      return connectDragSource(connectDropTarget(result));
    }

    return connectDragSource(result);
  }
});

module.exports = DragSource(DragTypes.TASK, cardSource, (connector, monitor) => ({
  connectDragSource: connector.dragSource(),
  connectDragPreview: connector.dragPreview(),
  isDragging: monitor.isDragging()
}))(DropTarget(DragTypes.TASK, cardTarget, (connector, monitor) => ({
  connectDropTarget: connector.dropTarget(),
  isOver: monitor.isOver()
}))(TaskCard));
