import React, { PropTypes } from 'react';
import moment from 'moment';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import classNames from 'classnames';

class Message extends React.Component
{
  static propTypes = {
    me:       PropTypes.object.isRequired,
    message:  PropTypes.object.isRequired,
    previous: PropTypes.object.isRequired
  };

  static renderSeparator(dateCreated) {
    const date = moment(dateCreated);
    let fromNow;
    if (date.fromNow(true) === 'a day') {
      fromNow = 'yesterday';
    } else {
      fromNow = date.fromNow();
    }
    return (
      <div className="ui horizontal divider">
        <span>{`${fromNow} ${date.format('MMM. D')}`}</span>
      </div>
    );
  }

  getMessage() {
    return {
      __html: this.props.message.message
    };
  }

  dateSep() {
    const date = moment(this.props.message.date_created);

    const previousDate = moment(this.props.previous.date_created);
    if ((this.props.previous && previousDate.dayOfYear() !== date.dayOfYear()) || !this.props.previous) {
      return Message.renderSeparator(this.props.message.date_created);
    }

    return null;
  }

  timestamp() {
    const m = moment(this.props.message.date_created);
    return (<div className="timestamp">
      {m.format('h:mm a')}
    </div>);
  }

  render() {
    const my = this.props.message.person === this.props.me.get('id');
    return (<Segment className={classNames('row', { my })}>
      {this.dateSep()}
      {my ? this.timestamp() : null}
      <div className="message">
        {!my ? <div className="agent name">{this.props.message.person_name}</div> : null}
        <div dangerouslySetInnerHTML={this.getMessage()} />
      </div>
      {!my ? this.timestamp() : null}
    </Segment>);
  }
}

export default Message;
