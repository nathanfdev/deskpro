import React from 'react';
import { DragSource, DropTarget } from 'react-dnd';
import $ from 'jquery';
import Formsy from 'formsy-react';
import FRC from 'DeskPRO/Component/FormComponents/main.js';
import DragTypes from '../../../Services/DragTypes.js';
import Picker from 'anytime';
import Moment from 'moment';
import { getEmptyImage } from 'react-dnd/modules/backends/HTML5';

import Card from '../../Application/Components/ListFrame/Card';

const cardTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();
    if (item.id !== props.task.id) {
      props.moveCard(item, props.task);
    }
  }
};

const cardSource = {
  beginDrag(props, monitor, component) {
    const width = $(React.findDOMNode(component)).width();

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
      subtype: 'list'
    };
  }
};

function collect(connector, monitor) {
  return {
    connectDragSource: connector.dragSource(),
    connectDragPreview: connector.dragPreview()
  };
}

const TaskCard = React.createClass({
  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function() {
    if (this.state.editing === true) {
      this.setState({
        editing: false
      });

      const task = this.state.task;
      task.taskId = this.props.task.id;

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

  toggleDetails: function() {
    this.setState({
      expanded: !this.state.expanded
    });
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
    this.props.updateMassActions(this.props.task.id);
  },

  componentDidMount: function() {
    const dueField = 'due-' + this.props.task.id;

    // Check if the due field actually exists before we try and add a date picker (e.g. on done tasks)
    if (typeof (this.refs[dueField]) !== 'undefined') {
      const dueButton = 'due-button-' + this.props.task.id;

      // Get the date for the task, and format it nicely
      const initial = this.props.task.date_due ? Moment(this.props.task.date_due).format() : null;

      // Create the picker
      const picker = new Picker({
        input: React.findDOMNode(this.refs[dueField]),
        button: React.findDOMNode(this.refs[dueButton]),
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
        task.taskId = this.props.task.id;

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
      linked_items,
      departments,
      teams,
      agents,
      source,
      connectDragSource,
      connectDropTarget,
      connectDragPreview
    } = this.props;

    const selected = this.props.selected;

    const detailsButtonText = this.state.expanded ? 'Collapse' : 'Expand';

    let ticketLink = undefined;
    let ticketTitle = 'Linked ticket';

    if (task.linked_items.length > 0) {
      task.linked_items.forEach((item) => {
        if (typeof linked_items[item].ticket !== 'undefined' && linked_items[item].ticket !== null) {
          ticketLink = '#' + linked_items[item].ticket;
          ticketTitle = this.props.tickets[linked_items[item].ticket].subject;
        }
      });
    }

    let assignee = null;
    let assigneeName = 'Unassigned';

    if (task.agents.length > 0) {
      // We assume one assignment for now, though we will need to support more later
      const agentId = task.agents[0];
      assignee = agents[agentId];
    } else if (task.teams.length > 0) {
      const teamId = task.teams[0];
      assignee = teams[teamId];
    } else if (task.departments.length > 0) {
      const departmentId = task.departments[0];
      assignee = departments[departmentId];
    }

    if (assignee) {
      if (assignee.name) {
        assigneeName = assignee.name;
      } else {
        assigneeName = assignee.title;
      }

      if (assignee.picture_blob) {
        assigneeName = (<span>
          <span className="list-icon">
            <span style={{backgroundImage: 'url(' + object.picture_blob.download_url + ')'}}
                  className="avatar"/>
          </span>
          {assigneeName}
        </span>);
      }
    }

    const titleClass = task.is_done ? 'dpwd--card-title strikethrough' : 'dpwd--card-title';

    const dueField = 'due-' + task.id;
    const dueButton = 'due-button-' + task.id;

    const overdue = Moment(task.date_due).isBefore();

    const placeHolder = this.props.isOver ? 'placeholder is-over' : 'placeholder';

    const result = (<div key={task.id}>
        <Card statusBars
              cardType="task"
              doneAction={this.props.toggleDone.bind(this, task, source)}
              task={task}>

          <div className="dpm--card-checkbox" onClick={this.toggleMassAction}>
            {selected ? <i className="fa fa-check" /> : '' }
          </div>

          <div className="dpw--card-line">
            <div className="dpw--card-line-left card-title">
              <div className={titleClass}>
                { !this.state.editing ?
                <h1 onDoubleClick={this.editMode}>{task.title}</h1> :
                  <Formsy.Form className="inline-form">
                  <h1><FRC.Input type="text" name="title" value={task.title} onChange={this.handleTitleChange} /></h1>
                  </Formsy.Form>
                }
              </div>
            </div>

            <div className="dpw--card-line-right">

              {task.is_done ?
              <div className="dpw--card-expand">
                <a href="#" onClick={this.toggleDetails}>{detailsButtonText} <i className="fa fa-navicon" /></a>
              </div>
              :
              assignee && assignee.picture_blob ?
              <div className="dpwd--card-assigned" onClick={this.props.toggleAssignWindow.bind(this, task)}>
                <span className="dpw--avatar-face" style={{backgroundImage: 'url(' + assignee.picture_blob.download_url + ')'}} />
              </div> : '' }
            </div>
          </div>

          {!task.is_done || this.state.expanded ?
          <div className="dpw--card-line">
            <div className="dpw--card-line-left">
              <span className={overdue ? 'overdue dpwd--card-line-item' : 'dpwd--card-line-item'} ref={dueButton}>
                <i className="fa fa-calendar-o" /> Due: {task.date_due ? this.dueIndicator(task.date_due) : 'N/A'}
                <input type="text" name="due-date" className="due-date-field" ref={dueField} disabled="disabled" />
              </span>

              {task.project && projects[task.project] ? <span>
                <span className="dpw--card-disc" />
                <span className="dpwd--card-line-item">
                  <i className="fa fa-book" /> {projects[task.project].title}
                </span>
              </span>
              : ''}

              {ticketLink ? <span>
                <span className="dpw--card-disc" />

                <span className="dpwd--card-line-item">
                  <i className="fa fa-link" /> <a href={ticketLink}>{ticketTitle}</a>
                </span>
              </span> : ''}
            </div>

            <div className="dpw--card-line-right">
              <span className="dpwd--card-line-item">
                {task.comment_count} <i className="fa fa-comment" />
              </span>

              {task.subtasks_total > 0 ?
                <span>
                  <span className="dpw--card-disc" />
                  <div>{task.subtasks_done}/{task.subtasks_total} <i className="fa fa-folder-open"/></div>
                </span>
              : ''}
            </div>
          </div>
          : '' }
        </Card>
        <div className={placeHolder} />
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
