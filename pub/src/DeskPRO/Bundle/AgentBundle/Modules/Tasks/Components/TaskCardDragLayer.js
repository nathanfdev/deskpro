import React, { PropTypes } from 'react';
import { DragLayer } from 'react-dnd';
import DragTypes from '../../../Services/DragTypes';

const layerStyles = {
  position: 'fixed',
  pointerEvents: 'none',
  zIndex: 100,
  left: 0,
  top: 0,
  width: '100%',
  height: '100%'
};

function getItemStyles(props) {
  const { currentOffset } = props;
  if (!currentOffset) {
    return {
      display: 'none'
    };
  }

  const { x, y } = currentOffset;
  const transform = `translate(${x}px, ${y}px)`;
  return {
    transform: transform,
    WebkitTransform: transform
  };
}

class TaskCardDragLayer {
  renderItem(type, item) {
    switch (type) {
      case DragTypes.TASK:

        return <p>Hello</p>;

        //const selected = this.props.selected;
        //
        //let cardClass = task.is_done ? "card task-card task-card-completed" : "card task-card";
        //let detailsButtonText = this.state.expanded ? "Collapse" : "Expand";
        //
        //let doneButton = task.is_done ? <span>Done <i className="fa fa-check" /></span> : "Mark Done";
        //
        //let ticket_link = undefined;
        //let ticket_title = 'Linked ticket';
        //
        //if (task.linked_items.length > 0) {
        //  task.linked_items.forEach((item) => {
        //    if (typeof linked_items[item].ticket !== 'undefined' && linked_items[item].ticket !== null) {
        //      ticket_link = '#' + linked_items[item].ticket;
        //      ticket_title = this.props.tickets[linked_items[item].ticket].subject;
        //    }
        //  });
        //}
        //
        //let assigneeId = "unassigned";
        //let assignee = "Unassigned";
        //
        //if (task.agents.length > 0) {
        //  // We assume one assignment for now, though we will need to support more later
        //  const agentId = task.agents[0];
        //
        //  assigneeId = "agents-" + agentId;
        //} else if (task.teams.length > 0) {
        //  const teamId = task.teams[0];
        //  assigneeId = "teams-" + teamId;
        //} else if (task.departments.length > 0) {
        //  const departmentId = task.departments[0];
        //  assigneeId = "departments-" + departmentId;
        //}
        //
        //return (<div className={cardClass} key={task.id} style={this.getStyles(this.props)}>
        //  <div>
        //    <div className="card-status-bar status-bar-left" />
        //    <div className="card-status-bar status-bar-right" />
        //
        //    <div className="card-checkbox">
        //      <span className="checkbox" />
        //    </div>
        //
        //    <div className="top-right-box">
        //      {!task.is_done ?
        //        <span className="assignment">
        //          { assignee }
        //        </span>:
        //        <button className="task-details-button">{detailsButtonText} <i
        //          className="fa fa-bars"/></button>}
        //    </div>
        //
        //    <div className="card-line">
        //      <span className="line-box card-task-mark">
        //        {doneButton}
        //      </span>
        //
        //      <h1>{task.title}</h1> :
        //    </div>
        //
        //    { this.state.expanded || !task.is_done ?
        //      <div className="card-line task-details">
        //        <div className="task-extras">
        //          <div>{task.comment_count} <i className="fa fa-comment"/></div>
        //
        //          {task.subtasks_total > 0 ?
        //            <span><span className="disc" />
        //    <div className="subtask-count">{task.subtasks_done}/{task.subtasks_total} <i className="fa fa-folder-open"/>
        //    </div></span> : ''}
        //        </div>
        //
        //        <div className="task-properties">
        //          <div className={overdue ? "overdue" : ""} ref={dueButton} >
        //
        //            <i className="fa fa-calendar-o" /> Due: {task.date_due ? <FormattedDate
        //            value={Date.parse(task.date_due)}
        //            day="numeric"
        //            month="long"
        //            year="numeric"
        //            />
        //            : 'N/A' }
        //            <input type="text" name="due-date" className="due-date-field" ref={dueField} disabled="disabled" />
        //          </div>
        //
        //          {task.project && projects[task.project] ? <span>
        //          <span className="disc" /><i className="fa fa-book"/> {projects[task.project].title}
        //        </span> : ''}
        //
        //          {ticket_link ? <span>
        //        <span className="disc"></span>
        //          <i className="fa fa-link"/><a href={ticket_link}>{ticket_title}</a>
        //        </span> : ''}
        //        </div>
        //      </div> : '' }
        //  </div>
        //</div>);
    }
  }

  render() {
    const { item, itemType, isDragging } = this.props;
    if (!isDragging) {
      return null;
    }

    console.log(item);

    return (
      <div style={layerStyles}>
        <div style={getItemStyles(this.props)}>
          {this.renderItem(itemType, item)}
        </div>
      </div>
    );
  }
}

TaskCardDragLayer.propTypes = {
  item: PropTypes.object,
  itemType: PropTypes.string,
  currentOffset: PropTypes.shape({
    x: PropTypes.number.isRequired,
    y: PropTypes.number.isRequired
  }),
  isDragging: PropTypes.bool.isRequired
};

function collect(monitor) {
  return {
    item: monitor.getItem(),
    itemType: monitor.getItemType(),
    currentOffset: monitor.getSourceClientOffset(),
    isDragging: monitor.isDragging()
  };
}

export default DragLayer(collect)(TaskCardDragLayer);