import React from 'react';
import ReactDOM from 'react-dom';
import { DragSource, DropTarget } from 'react-dnd';
import jQuery from 'jquery';
import DragTypes from '../../../Services/DragTypes.js';
import Picker from 'anytime';
import Moment from 'moment';
import { getEmptyImage } from 'react-dnd-html5-backend';

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

const TaskCondensedCard = React.createClass({
  mixins: [
    require('react-onclickoutside')
  ],

  propTypes: {
    task: React.PropTypes.object,
    editTask: React.PropTypes.func,
    updateMassActions: React.PropTypes.func,
    source: React.PropTypes.string,
    order: React.PropTypes.string,
    projects: React.PropTypes.object,
    linked_items: React.PropTypes.object,
    departments: React.PropTypes.object,
    teams: React.PropTypes.object,
    agents: React.PropTypes.object,
    selected: React.PropTypes.bool,
    isOver: React.PropTypes.bool,
    connectDragPreview: React.PropTypes.func,
    connectDragSource: React.PropTypes.func,
    connectDropTarget: React.PropTypes.func
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

  handleAssigneeChange: function(value) {
    const task = this.state.task;
    const assignment = value.target.value;

    task.agents = [];
    task.teams = [];
    task.departments = [];

    if (assignment !== 'unassigned') {
      const assignmentParts = assignment.split('-');
      task[assignmentParts[0]] = [assignmentParts[1]];
    }

    this.setState({
      task: task
    });

    task.taskId = this.props.task.get('id');

    this.props.editTask(this.props.source, task);
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
      const initial = this.props.task.get('date_due') ? Moment(this.props.task.get('date_due')).format() : null;

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

  render: function() {
    const { task,
      projects,
      connectDragSource,
      connectDropTarget
    } = this.props;

    const selected = this.props.selected;

    let assignee = null;

    if (task.has('agents') && task.get('agents').size > 0) {
      assignee = this.props.agents.get(task.get('agents').get(0)).get('name');
    } else if (task.has('teams') && task.get('teams').size > 0) {
      assignee = this.props.teams.get(task.get('teams').get(0)).get('name');
    } else if (task.has('departments') && task.get('departments').size > 0) {
      assignee = this.props.departments.get(task.get('departments').get(0)).get('title');
    }

    let taskClass = task.get('is_done') ? 'ticket done' : 'ticket';
    taskClass = this.props.isOver ? taskClass + ' is-over' : taskClass;

    const result = (<tr key={task.get('id')} className={taskClass}>
          <td>
            <span className="checkbox" onClick={this.toggleMassAction}>
              <i className={selected ? 'fa fa-check selected' : 'fa fa-check'} />
            </span>
            <a href="#">{task.get('title')}</a>
          </td>
          <td>{task.get('project') && projects.has(task.get('project')) ? projects.get(task.get('project')).get('title') : ''}</td>
          <td>{task.get('date_due') ? Moment(task.get('date_due')).format('DD/MM/YY') : '' }</td>
          <td>{assignee}</td>
        </tr>);

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
