import React, { PropTypes } from 'react';
import TimeAgo from 'react-timeago';
import { PersonAvatar } from '../../../Common/Components/Avatar/index';

export class Message extends React.Component {

  static propTypes = {
    me: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    message: PropTypes.object.isRequired
  };

  renderMy = () => {
    let className = 'chat-message yours';
    if(this.props.message.old === true) {
      className += ' old';
    }
    return (
      <li className={className}>
        <div className="message-read-mark">
          <i className="fa fa-check"></i>
          {(this.props.message.status > 1) ? <i className="fa fa-check"></i> : null}
        </div>
        <span className="time"><TimeAgo date={this.props.message.date_created}/> <i className="fa fa-clock-o"></i></span>
        <p>{this.props.message.message}</p>
      </li>
    );
  };

  renderNotMy = () => {
    const author = this.props.agents.get(this.props.message.person_id);
    let className = 'chat-message';
    if(this.props.message.old === true) {
      className += ' old';
    }
    return (
      <li className={className}>
        <a title={this.props.message.person_name} className="chat-avatar">
          <PersonAvatar person={author} size="22"/>
        </a>
        <span className="time"><TimeAgo date={this.props.message.date_created}/> <i className="fa fa-clock-o"></i></span>
        <p>{this.props.message.message}</p>
      </li>
    );
  };

  render() {
    return (this.props.message.person_id === this.props.me.get('id')) ? this.renderMy() : this.renderNotMy();
  }
}