import React from 'react';
import Moment from 'moment';
import { Card } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';

export default class TaskCardGeneric extends React.Component {

  static propTypes = {
    agents: React.PropTypes.object,
    departments: React.PropTypes.object,
    projects: React.PropTypes.object,
    task: React.PropTypes.object,
    teams: React.PropTypes.object,
    tickets: React.PropTypes.object
  }

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
          } = this.props;
    const titleClass = task.get('is_done', false) ? 'dpwd--card-title strikethrough' : 'dpwd--card-title';
    const overdue = Moment(task.get('date_due')).isBefore();

    let ticketLink = undefined;
    let ticketTitle = 'Linked ticket';

    if (task.has('linked_tickets') && task.get('linked_tickets').size > 0) {
      task.get('linked_tickets').forEach((item) => {
        if (tickets.has(item)) {
          ticketLink = '#' + item;
          ticketTitle = tickets.get(item).get('subject');
        }
      });
    }

    let assignee = null;

    if (task.has('agents') && task.get('agents').size > 0) {
      // We assume one assignment for now, though we will need to support more later
      const agentId = task.get('agents').first();
      assignee = agents.get(agentId);
    } else if (task.has('teams') && task.get('teams').size > 0) {
      const teamId = task.get('teams').first();
      assignee = teams.get(teamId);
    } else if (task.has('departments') && task.get('departments').size > 0) {
      const departmentId = task.get('departments').first();
      assignee = departments.get(departmentId);
    }

    return (<Card statusBars={false}
            type="floating">
      <div className="dpw--card-line">
        <div className="dpw--card-line-left card-title">
          <div className={titleClass}>
            <h1>{task.get('title')}</h1>
          </div>
        </div>

        { assignee && assignee.has('picture_blob') && assignee.get('picture_blob') ?
        <div className="dpw--card-line-right">
          <div className="dpwd--card-assigned">
            <span className="dpw--avatar-face" style={{backgroundImage: 'url(' + assignee.get('picture_blob').get('download_url') + ')'}} />
          </div>
        </div> : '' }
      </div>

      <div className="dpw--card-line">
        <div className="dpw--card-line-left">
          <span className={overdue ? 'overdue dpwd--card-line-item' : 'dpwd--card-line-item'}>
            <i className="fa fa-calendar-o" /> Due: {task.get('date_due') ? this.dueIndicator(task.get('date_due')) : 'N/A'}
          </span>

          {task.has('project') && projects.get(task.get('project')) ? <span>
            <span className="dpw--card-disc" />
            <span className="dpwd--card-line-item">
              <i className="fa fa-book" /> {projects.get(task.get('project')).get('title')}
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
            {task.get('comment_count', 0)} <i className="fa fa-comment" />
          </span>

          {task.get('subtasks_total') > 0 ?
            <span className="dpwd--card-line-item">
              <div><span className="dpw--card-disc" /> {task.get('subtasks_done')}/{task.get('subtasks_total')} <i className="fa fa-folder-open"/></div>
            </span>
          : ''}
        </div>
      </div>
    </Card>);
  }
}
