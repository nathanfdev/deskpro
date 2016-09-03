import React, { PropTypes } from 'react';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import TimeAgo from 'react-timeago';

export class Message extends React.Component
{
  static propTypes = {
    me:      PropTypes.object.isRequired,
    message: PropTypes.object.isRequired
  };

  getMessage() {
    return {
      __html: this.props.message.message
    };
  }

  timestamp() {
    return (<div className="timestamp">
      <TimeAgo date={this.props.message.date_created} />
    </div>);
  }

  render() {
    const my = this.props.message.person === this.props.me.get('id');
    return (<Segment classes={['row', { my }]}>
      {my ? this.timestamp() : null}
      <div className="message">
        {!my ? <div className="agent name">{this.props.message.person_name}</div> : null}
        <div dangerouslySetInnerHTML={this.getMessage()}></div>
      </div>
      {!my ? this.timestamp() : null}
    </Segment>);
  }
}
