import React from 'react';
import Moment from 'moment';
import Card from '../../Application/Components/ListFrame/Card';

export default class TaskCardGeneric extends React.Component {

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

  render() {
    const { task,
            projects,
            tickets,
            agents,
            teams,
            departments,
            linkedItems
          } = this.props;
    const titleClass = task.is_done ? 'dpwd--card-title strikethrough' : 'dpwd--card-title';
    const overdue = Moment(task.date_due).isBefore();

    let ticketLink = undefined;
    let ticketTitle = 'Linked ticket';

    if (task.linked_items && task.linked_items.length > 0) {
      task.linked_items.forEach((item) => {
        if (typeof linkedItems[item].ticket !== 'undefined' && linkedItems[item].ticket !== null) {
          ticketLink = '#' + linkedItems[item].ticket;
          ticketTitle = tickets[linkedItems[item].ticket].subject;
        }
      });
    }

    let assignee = null;

    if (task.agents && task.agents.length > 0) {
      // We assume one assignment for now, though we will need to support more later
      const agentId = task.agents[0];
      assignee = agents[agentId];
    } else if (task.teams && task.teams.length > 0) {
      const teamId = task.teams[0];
      assignee = teams[teamId];
    } else if (task.departments && task.departments.length > 0) {
      const departmentId = task.departments[0];
      assignee = departments[departmentId];
    }

    return (
      <Card statusBars
            cardType="float"
            task={task} >
        <div className="dpw--card-line">
          <div className="dpw--card-line-left card-title">
            <div className={titleClass}>
              <h1>{task.title}</h1>
            </div>
          </div>

          { assignee && assignee.picture_blob ?
          <div className="dpw--card-line-right">
            <div className="dpwd--card-assigned">
              <span className="dpw--avatar-face" style={{backgroundImage: 'url(' + assignee.picture_blob.download_url + ')'}} />
            </div>
          </div> : '' }
        </div>

        <div className="dpw--card-line">
          <div className="dpw--card-line-left">
            <span className={overdue ? 'overdue dpwd--card-line-item' : 'dpwd--card-line-item'}>
              <i className="fa fa-calendar-o" /> Due: {task.date_due ? this.dueIndicator(task.date_due) : 'N/A'}
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
              <span className="dpwd--card-line-item">
                <div><span className="dpw--card-disc" /> {task.subtasks_done}/{task.subtasks_total} <i className="fa fa-folder-open"/></div>
              </span>
            : ''}
          </div>
        </div>
      </Card>
    );
  }
}
