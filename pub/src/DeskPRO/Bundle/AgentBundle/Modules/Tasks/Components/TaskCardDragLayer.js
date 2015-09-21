import React, { PropTypes } from 'react';
import { DragLayer } from 'react-dnd';
import Moment from 'moment';
import { FormattedDate } from 'react-intl';
import Card from '../../Application/Components/ListFrame/Card';

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
    WebkitTransform: transform,
  };
}

class TaskCardDragLayer {
  dueIndicator(due) {
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
  }

  renderItem(type, item) {
    switch (item.subtype) {
      case 'list':

        let cardClass = item.details.is_done ? 'card task-card task-card-completed moving' : 'card task-card moving';

        let doneButton = item.details.is_done ? <span>Done <i className="fa fa-check" /></span> : 'Mark Done';

        let ticketLink = undefined;
        let ticketTitle = 'Linked ticket';

        if (item.details.tickets && item.details.tickets.length > 0) {
          ticketTitle = item.details.tickets[0].subject;
          ticketLink = '#';
        }

        const titleClass = item.details.is_done ? 'dpwd--card-title strikethrough' : 'dpwd--card-title';

        let assignee = null;

        if (item.details.agents && item.details.agents.length > 0) {
          // We assume one assignment for now, though we will need to support more later
          const agentId = item.details.agents[0];

          assignee = item.agents[agentId];
        } else if (item.details.teams && item.details.teams.length > 0) {
          const teamId = item.details.teams[0];
          assignee = item.teams[teamId];
        } else if (item.details.departments && item.details.departments.length > 0) {
          const departmentId = item.details.departments[0];
          assignee = item.departments[departmentId];
        }

        const overdue = Moment(item.details.date_due).isBefore();

        return (<Card statusBars
              moving
              cardType="task"
              task={item.details}>

          <div className="dpm--card-checkbox">

          </div>

          <div className="dpw--card-line">
            <div className="dpw--card-line-left card-title">
              <div className={titleClass}>
                <h1>{item.details.title}</h1>
              </div>
            </div>

            <div className="dpw--card-line-right">

              {item.details.is_done ?
              <div className="dpw--card-expand">
                <a href="#">{detailsButtonText} <i className="fa fa-navicon" /></a>
              </div>
              :
              assignee && assignee.picture_blob ?
              <div className="dpwd--card-assigned">
                <span className="dpw--avatar-face" style={{backgroundImage: 'url(' + item.details.picture_blob.download_url + ')'}} />
              </div> : '' }
            </div>
          </div>

          {!item.details.is_done || this.state.expanded ?
          <div className="dpw--card-line">
            <div className="dpw--card-line-left">
              <span className={overdue ? 'overdue dpwd--card-line-item' : 'dpwd--card-line-item'}>
                <i className="fa fa-calendar-o" /> Due: {item.details.date_due ? this.dueIndicator(item.details.date_due) : 'N/A'}
              </span>

              {item.details.project && item.projects[item.details.project] ? <span>
                <span className="dpw--card-disc" />
                <span className="dpwd--card-line-item">
                  <i className="fa fa-book" /> {item.projects[item.details.project].title}
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
                {item.details.comment_count} <i className="fa fa-comment" />
              </span>

              {item.details.subtasks_total > 0 ?
                <span>
                  <span className="dpw--card-disc" />
                  <div>{item.details.subtasks_done}/{item.details.subtasks_total} <i className="fa fa-folder-open"/></div>
                </span>
              : ''}
            </div>
          </div>
          : '' }
        </Card>);
        break;
      case 'kanban':
        let assigneeName = '';

        if (item.details.agents && item.details.agents.length > 0) {
          assigneeName = item.agents[item.details.agents[0]].name;
        } else if (item.details.teams && item.details.teams.length > 0) {
          assigneeName = item.teams[item.details.teams[0]].name;
        } else if (item.details.departments && item.details.departments.length > 0) {
          assigneeName = item.departments[item.details.departments[0]].title;
        }

        return (<div className="kanban">
          <div className="card task-card moving" style={{width: item.width}}>
            <div>
              <div className="card-status-bar status-bar-left" />
              <div className="card-status-bar status-bar-right" />

              <div className="card-checkbox">
                <span className="checkbox" />
              </div>

              <div className="content">
                <h1 className={item.details.is_done ? 'complete' : ''}>{item.details.title}</h1>

                <div className="card-line task-details">
                  <div className="top-right-box">
                <span className="assignment">
                  {assigneeName}
                </span>
                  </div>
                  <div>
                    <i className="fa fa-calendar-o" /> Due: {item.details.date_due ? <FormattedDate
                    value={Date.parse(item.details.date_due)}
                    day="numeric"
                    month="long"
                    year="numeric"
                    />
                    : 'N/A' }
                  </div>
                </div>
                <hr/>
                <div className="card-line task-properties">
                  <span>{item.details.comment_count} <i className="fa fa-comment"/></span>

                  {item.details.subtasks_total > 0 ?
                    <span>
                  <span className="disc"/>
                    <div className="subtask-count">{item.details.subtasks_done}/{item.details.subtasks_total} <i className="fa fa-folder-open"/></div>
                  </span>
                      : ''}
                  </div>
                </div>
              </div>
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