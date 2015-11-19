import React, { PropTypes } from 'react';
import { DragLayer } from 'react-dnd';
import Moment from 'moment';
import { FormattedDate } from 'react-intl';
import { Card } from '../../Common/Components/ListFrame/View/Card';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { AgentTeamAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/AgentTeamAvatar';
import { DepartmentAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/DepartmentAvatar';

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

export default class TaskCardDragLayer extends React.Component {
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
        let ticketLink = undefined;
        let ticketTitle = 'Linked ticket';

        if (item.details.get('tickets') && item.details.get('tickets').size > 0 && item.details.get('tickets').get(0)) {
          const ticketId = item.details.get('tickets').get(0);
          ticketTitle = item.tickets.get(ticketId).get('subject');
          ticketLink = '#';
        }

        const titleClass = item.details.get('is_done') ? 'dpwd--card-title strikethrough' : 'dpwd--card-title';

        let assignee = null;
        let assigneeAvatar = null;

        if (item.details.has('agents') && item.details.get('agents').size > 0) {
          // We assume one assignment for now, though we will need to support more later
          const agentId = item.details.get('agents').first();
          assignee = item.agents.get(agentId);
          assigneeAvatar = (<PersonAvatar person={assignee} size="16" />);
        } else if (item.details.has('teams') && item.details.get('teams').size > 0) {
          const teamId = item.details.get('teams').first();
          assignee = item.teams.get(teamId);
          assigneeAvatar = (<AgentTeamAvatar agentTeam={assignee} size="16" />);
        } else if (item.details.has('departments') && item.details.get('departments').size > 0) {
          const departmentId = item.details.get('departments').first();
          assignee = item.departments.get(departmentId);
          assigneeAvatar = (<DepartmentAvatar department={assignee} size="16" />);
        }

        const overdue = Moment(item.details.get('date_due')).isBefore();

        return (<Card minimized={item.details.get('is_done')} moving type="task">
        {
          item.details.get('is_done') ?
          <div className="dpw--single-card-mark-done dpw--single-card-mark-done-minimized">
            <span>Done</span>
            <i className="fa fa-check"/>
          </div>
            :
          <div className="dpw--single-card-mark-done">
            <i className="fa fa-check"/>
            <span>Mark Done</span>
          </div>
        }

        <div className="dpm--card-checkbox">
          <i className="fa fa-check"/>
        </div>

        <div className="dpw--card-line">
          <div className="dpw--card-line-left card-title">
            <div className={titleClass}>
              <h1>{item.details.get('title')}</h1>
            </div>
          </div>

          <div className="dpw--card-line-right">
            {item.details.get('is_done') ?
              <div className="dpw--card-expand">
                <a href="#">Expand <i className="fa fa-navicon"/></a>
              </div>
              :
              assignee ?
              <div className="dpwd--card-assigned">
                <div className="dpw--avatar-face" style={{position: 'relative'}}>{assigneeAvatar}</div>
              </div> : '' }
          </div>
        </div>

        {!item.details.get('is_done') ?
         <div className="dpw--card-line">
           <div className="dpw--card-line-left">
              <span className={overdue ? 'overdue dpwd--card-line-item' : 'dpwd--card-line-item'}>
                <i className="fa fa-calendar-o"/> Due: {item.details.get('date_due') ? this.dueIndicator(item.details.get('date_due')) : 'N/A'}
              </span>

              {item.details.get('project') && item.projects.get(item.details.get('project')) ? <span>
                <span className="dpw--card-disc"/>
                <span className="dpwd--card-line-item">
                  <i className="fa fa-book"/> {item.projects.get(item.details.get('project')).get('title')}
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
                {item.details.get('comment_count')} <i className="fa fa-comment"/>
              </span>

              {item.details.get('subtasks_total') > 0 ?
              <span className="dpwd--card-line-item">
                  <div><span className="dpw--card-disc"/> {item.details.get('subtasks_done')}/{item.details.get('subtasks_total')} <i
                    className="fa fa-folder-open"/></div>
                </span>
               : ''}
           </div>
         </div>
          : '' }
        </Card>);
      case 'kanban':
        let assigneeName = '';

        if (item.details.get('agents') && item.details.get('agents').size > 0) {
          assigneeName = item.agents[item.details.get('agents').get(0)].name;
        } else if (item.details.get('teams') && item.details.get('teams').size > 0) {
          assigneeName = item.teams[item.details.get('teams').get(0)].name;
        } else if (item.details.get('departments') && item.details.get('departments').size > 0) {
          assigneeName = item.departments[item.details.get('departments').get(0)].title;
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
                <h1 className={item.details.get('is_done') ? 'complete' : ''}>{item.details.get('title')}</h1>

                <div className="card-line task-details">
                  <div className="top-right-box">
                <span className="assignment">
                  {assigneeName}
                </span>
                  </div>
                  <div>
                    <i className="fa fa-calendar-o" /> Due: {item.details.get('date_due') ? <FormattedDate
                    value={Date.parse(item.details.get('date_due'))}
                    day="numeric"
                    month="long"
                    year="numeric"
                    />
                    : 'N/A' }
                  </div>
                </div>
                <hr/>
                <div className="card-line task-properties">
                  <span>{item.details.get('comment_count')} <i className="fa fa-comment"/></span>

                  {item.details.get('subtasks_total') > 0 ?
                    <span>
                  <span className="disc"/>
                    <div className="subtask-count">{item.details.get('subtasks_done')}/{item.details.get('subtasks_total')} <i className="fa fa-folder-open"/></div>
                  </span>
                      : ''}
                  </div>
                </div>
              </div>
            </div>
          </div>);
      default:
        return null;
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
