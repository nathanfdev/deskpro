import React from 'react';
import Moment from 'moment';

export default class TaskCardGeneric extends React.Component {

  render() {
    const {task, massActionable, projects, tickets} = this.props;

    let ticketLink = undefined;
    let ticketTitle = 'Linked ticket';

    if (task.linked_items && task.linked_items.length > 0) {
      task.linked_items.forEach((item) => {
        if (typeof linked_items[item].ticket !== 'undefined' && linked_items[item].ticket !== null) {
          ticketLink = '#' + linked_items[item].ticket;
          ticketTitle = tickets[linked_items[item].ticket].subject;
        }
      });
    }

    return (
        <div className="dpmw--single-card">

        {massActionable ?
          <div className="dpm--card-checkbox">
            <i className="fa fa-check" />
          </div>
        : ''}

        <div className="dpw--card-line">
          <div className="dpw--card-line-left">
            <div className="dpwd--card-title">
              <h1>{task.title}</h1>
            </div>
          </div>

          <div className="dpw--card-line-right">
          {task.picture_blob ?
            <div className="dpwd--card-assigned">
              <span className="dpw--avatar-face" style={{backgroundImage: 'url(' + task.picture_blob.download_url + ')'}} />
            </div> : '' }
          </div>
        </div>

        <div className="dpw--card-line">
          <div className="dpw--card-line-left">

            <span className="dpwd--card-line-item">
              <i className="fa fa-calendar-o" /> Due: {task.date_due ? Moment(task.date_due).format('MMMM D, YYYY')
                  : 'N/A' }
            </span>

            {task.project ?
            <span>
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
            </span>
            : ''}
          </div>

          <div className="dpw--card-line-right">
            <span className="dpwd--card-line-item">
              {task.comment_count} <i className="fa fa-comment" />
            </span>

            {task.subtasks_total > 0 ?
            <span>
              <span className="dpw--card-disc" />
              <span className="dpwd--card-line-item">
                  <div>{task.subtasks_done}/{task.subtasks_total} <i className="fa fa-folder-open" /></div>
              </span>
            </span>
            : '' }
          </div>
        </div>
      </div>
    );
  }
}
