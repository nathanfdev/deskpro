import React from 'react';

export class ChatCard extends React.Component {

  getTimeInterval(date) {
    let created = new Date(date),
      now = new Date(),
      interval = now.getTime() - created.getTime(),
      inHours = interval / (1000 * 60 * 60);
    return Math.round(inHours);
  }

  render() {
    const {chat} = this.props;

    return (
      <div className="card chat-card">
        <div className="card-status-bar status-bar-left level-8"></div>
        <div className="card-status-bar status-bar-right level-8"></div>

        <div className="card-checkbox">
          <span className="checkbox"><i className="fa fa-check"></i></span>
        </div>

        <div className="card-line">
          <span className="line-box">
            <span className="chat-id">#{chat.id}</span>
          </span>

          <div className="task-extras">
            <span className="text">
              {this.getTimeInterval(chat.date_created)} hrs ago
            </span>
          </div>
        </div>

        <div className="card-line">
          <div className="ticket-intro">
            <h1>{chat.subject}</h1>
          </div>
        </div>

        <div className="card-line">
          <div className="task-extras">
            <span className="text"></span>
            <span className="chat-avatar" style={{backgroundImage: "url('./img/avatar6.png')"}}></span>
            <span className="disc"></span>
            <span className="text"></span> <i className="fa fa-comment"></i>
          </div>

          <div className="task-properties">
            <i className="fa fa-book"></i> <span className="chat-type"></span>
            <span className="disc"></span>
            <i className="fa fa-book"></i> <span
            className="chat-custom-category"></span>
            <span className="disc"></span>
          </div>
        </div>
      </div>
    );
  }
}