import PropTypes from 'prop-types';
import React from 'react';
import TimeAgo from '@deskpro/react-timeago';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import moment from 'moment';
import { replaceSmileCodes } from 'DeskPRO/Component/Rte/Emotions';

export class Message extends React.Component {

  static propTypes = {
    current:         PropTypes.number.isRequired,
    size:            PropTypes.number.isRequired,
    me:              PropTypes.object.isRequired,
    agents:          PropTypes.object.isRequired,
    message:         PropTypes.object.isRequired,
    previousMessage: PropTypes.object.isRequired
  };

  getMessage = () => ({
    __html: replaceSmileCodes(this.props.message.message)
  });

  dateSep() {
    const date = moment(this.props.message.date_created);

    const previousDate = moment(this.props.previousMessage.date_created);
    if (this.props.previousMessage && previousDate.dayOfYear() !== date.dayOfYear()) {
      return this.renderSeparator(this.props.previousMessage.date_created);
    }
  }

  renderSeparator(date_created) {
    const date = moment(date_created);
    let fromNow;
    if (date.fromNow(true) === 'a day') {
      fromNow = 'yesterday';
    } else {
      fromNow = date.fromNow();
    }
    return (
      <li className="chat-divider">
        <span>{`${fromNow} ${date.format('MMM. D')}`}</span>
        <hr />
      </li>
    );
  }

  renderMy = () => {
    let className = 'chat-message yours';
    if (this.props.message.old === true) {
      className += ' old';
    }
    return (
      <li className={className}>
        <div className="message-read-mark">
          {this.props.message.status > 0 && <i className="fa fa-check" />}
          {this.props.message.status > 1 && <i className="fa fa-check" />}
        </div>
        <span className="time">
          <TimeAgo date={this.props.message.date_created} />
          <i className="fa fa-clock-o" />
        </span>
        <div className="message-content" dangerouslySetInnerHTML={this.getMessage()} />
      </li>
    );
  };

  renderNotMy = () => {
    const author = this.props.agents.get(this.props.message.person);
    let className = 'chat-message';
    if (this.props.message.old === true) {
      className += ' old';
    }
    return (
      <li className={className}>
        <a title={this.props.message.person_name} className="chat-avatar">
          <PersonAvatar person={author} size={22} />
        </a>
        <span className="time">
          <TimeAgo date={this.props.message.date_created} />
          <i className="fa fa-clock-o" />
        </span>
        <div className="message-content" dangerouslySetInnerHTML={this.getMessage()} />
      </li>
    );
  };

  render() {
    return (
      <span>
        {this.dateSep()}
        {this.props.message.person === this.props.me.get('id') ? this.renderMy() : this.renderNotMy()}
      </span>);
  }
}
