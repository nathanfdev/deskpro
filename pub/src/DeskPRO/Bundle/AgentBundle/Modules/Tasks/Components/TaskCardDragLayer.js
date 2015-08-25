import React, { PropTypes } from 'react';
import { DragLayer } from 'react-dnd';
import DragTypes from '../../../Services/DragTypes';
import Moment from "moment";
import { IntlMixin, FormattedDate } from "react-intl";
import $ from "jquery";

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

        //return <p>Hello</p>;

        let cardClass = item.details.is_done ? "card task-card task-card-completed moving" : "card task-card moving";

        let doneButton = item.details.is_done ? <span>Done <i className="fa fa-check" /></span> : "Mark Done";

        let ticket_link = undefined;
        let ticket_title = 'Linked ticket';

        if (item.details.tickets && item.details.tickets.length > 0) {
          ticket_title = item.details.tickets[0].subject;
          ticket_link = '#';
        }

        let assignee = "Unassigned";

        if (item.details.agents && item.details.agents.length > 0) {
          // We assume one assignment for now, though we will need to support more later
          const agentId = item.details.agents[0];

          assignee = item.agents[agentId].name;
        } else if (item.details.teams && item.details.teams.length > 0) {
          const teamId = item.details.teams[0];
          assignee = item.teams[teamId].name;
        } else if (item.details.departments && item.details.departments.length > 0) {
          const departmentId = item.details.departments[0];
          assignee = item.departments[departmentId].title;
        }

        const overdue = Moment(item.details.date_due).isBefore();

        return (<div className={cardClass} key={item.details.id} style={{width: item.width}}>
          <div>
            <div className="card-status-bar status-bar-left" />
            <div className="card-status-bar status-bar-right" />

            <div className="card-checkbox">
              <span className="checkbox" />
            </div>

            <div className="top-right-box">
              {!item.details.is_done ?
                <span className="assignment">
                  { assignee }
                </span>:
                <button className="task-details-button">Expand <i
                  className="fa fa-bars"/></button>}
            </div>

            <div className="card-line">
              <span className="line-box card-task-mark">
                {doneButton}
              </span>

              <h1>{item.details.title}</h1>
            </div>

            { !item.details.is_done ?
              <div className="card-line task-details">
                <div className="task-extras">
                  <div>{item.details.comment_count} <i className="fa fa-comment"/></div>

                  {item.details.subtasks_total > 0 ?
                  <span><span className="disc" />
                  <div className="subtask-count">{item.details.subtasks_done}/{item.details.subtasks_total} <i className="fa fa-folder-open"/></div>
                  </span> : ''}
                </div>

                <div className="task-properties">
                  <div className={overdue ? "overdue" : ""}>

                    <i className="fa fa-calendar-o" /> Due: {item.details.date_due ? <FormattedDate
                    value={Date.parse(item.details.date_due)}
                    day="numeric"
                    month="long"
                    year="numeric"
                    />
                    : 'N/A' }
                  </div>

                  {item.details.project && item.projects[item.details.project] ? <span>
                  <span className="disc" /><i className="fa fa-book"/> {item.projects[item.details.project].title}
                  </span> : ''}

                  {ticket_link ? <span>
                  <span className="disc"></span>
                    <i className="fa fa-link"/><a href={ticket_link}>{ticket_title}</a>
                  </span> : ''}
                </div>
              </div> : '' }
          </div>
        </div>);
    }
  }

  render() {
    const { item, itemType, isDragging } = this.props;
    if (isDragging) {
      return (
        <div style={layerStyles}>
          <div style={getItemStyles(this.props)}>
            {this.renderItem(itemType, item)}
          </div>
        </div>
      );
    }

    return null;
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