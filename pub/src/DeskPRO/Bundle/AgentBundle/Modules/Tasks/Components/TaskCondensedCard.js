import React from "react";
import { DragSource, DropTarget } from "react-dnd";
import { connect } from 'react-redux';
import $ from 'jquery';
import * as TaskActions from "../Actions/TaskListActions";
import { IntlMixin, FormattedDate } from "react-intl";
import Formsy from "formsy-react";
import FRC from "../../../../../Component/FormComponents/main.js";
import DragTypes from "../../../Services/DragTypes.js";
import Picker from "anytime";
import Moment from "moment";
import { getEmptyImage } from 'react-dnd/modules/backends/HTML5';

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

function collect(connect, monitor) {
  return {
    connectDragSource: connect.dragSource(),
    connectDragPreview: connect.dragPreview()
  };
}

const TaskCondensedCard = React.createClass({
  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function(evt) {
    if (this.state.editing === true) {
      this.setState({
        editing: false
      });

      let task = this.state.task;
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

  toggleDetails: function () {
    this.setState({
      expanded: !this.state.expanded
    });
  },

  editMode: function () {
    this.setState({
      editing: true
    });
  },

  handleTitleChange: function (name, value) {
    let task = this.state.task;
    task.title = value;
    this.setState({
      task: task
    });
  },

  handleAssigneeChange: function(value) {
    let task = this.state.task;
    const assignment = value.target.value;

    task.agents = [];
    task.teams = [];
    task.departments = [];

    if (assignment !== 'unassigned') {
      let assignmentParts = assignment.split('-');
      task[assignmentParts[0]] = [assignmentParts[1]];
    }

    this.setState({
      task: task
    });

    task.taskId = this.props.task.id;

    this.props.editTask(this.props.source, task);
  },

  toggleMassAction: function(event) {
    this.props.updateMassActions(this.props.task.id);
  },

  componentDidMount: function() {
    const dueField = "due-" + this.props.task.id;

    // Check if the due field actually exists before we try and add a date picker (e.g. on done tasks)
    if (typeof (this.refs[dueField]) !== 'undefined') {
      const dueButton = "due-button-" + this.props.task.id;

      // Get the date for the task, and format it nicely
      const initial = this.props.task.date_due ? Moment(this.props.task.date_due).format() : null;

      // Create the picker
      let picker = new Picker({
        input: React.findDOMNode(this.refs[dueField]),
        button: React.findDOMNode(this.refs[dueButton]),
        initialValue: initial,
        format: "hh:mm, MMMM D, YYYY"
      });
      picker.render();

      // Change the component state and submit the edit when the date is changed
      picker.on('change', (newDate) => {
        let task = this.state.task;
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

  render: function () {
    const { task,
      projects,
      linked_items,
      departments,
      teams,
      agents,
      source,
      connectDragSource,
      connectDragPreview,
      connectDropTarget
    } = this.props;

    const selected = this.props.selected;

    let detailsButtonText = this.state.expanded ? "Collapse" : "Expand";

    let doneButton = task.is_done ? <span>Done <i className="fa fa-check" /></span> : "Mark Done";

    let ticket_link = undefined;
    let ticket_title = 'Linked ticket';

    if (task.linked_items.length > 0) {
      task.linked_items.forEach((item) => {
        if (typeof linked_items[item].ticket !== 'undefined' && linked_items[item].ticket !== null) {
          ticket_link = '#' + linked_items[item].ticket;
          ticket_title = this.props.tickets[linked_items[item].ticket].subject;
        }
      });
    }

    let assignee = null;

    if (task.agents && task.agents.length > 0) {
      assignee = this.props.agents[task.agents[0]].name;
    } else if (task.teams && task.teams.length > 0) {
      assignee = this.props.teams[task.teams[0]].name;
    } else if (task.departments && task.departments.length > 0) {
      assignee = this.props.departments[task.departments[0]].title;
    }

    let taskClass = task.is_done ? "ticket done" : "ticket";
        taskClass = this.props.isOver ? taskClass + " is-over" : taskClass;

    const dueField = "due-" + task.id;
    const dueButton = "due-button-" + task.id;

    const overdue = Moment(task.date_due).isBefore();

    const result = <tr key={task.id} className={taskClass}>
      <td>
        <span className="checkbox" onClick={this.toggleMassAction}>
          <i className={selected ? "fa fa-check selected" : "fa fa-check"} />
        </span>
        <a href="#">{task.title}</a>
      </td>
      <td>{task.project && projects[task.project] ? projects[task.project].title : ''}</td>
      <td>{task.date_due ? Moment(task.date_due).format('DD/MM/YY') : '' }</td>
      <td>{assignee}</td>
    </tr>;

    if (this.props.order === 'list') {
      return connectDragSource(connectDropTarget(result));
    }
    return connectDragSource(result);
  }
});

module.exports = DragSource(DragTypes.TASK, cardSource, (connect, monitor) => ({
  connectDragSource: connect.dragSource(),
  connectDragPreview: connect.dragPreview(),
  isDragging: monitor.isDragging()
}))(DropTarget(DragTypes.TASK, cardTarget, (connect, monitor) => ({
  connectDropTarget: connect.dropTarget(),
  isOver: monitor.isOver()
}))(TaskCondensedCard));
