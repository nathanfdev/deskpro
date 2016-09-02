import React, { PropTypes } from 'react';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';

class Message extends React.Component
{
  static propTypes = {
    me:          PropTypes.object.isRequired,
    id:          PropTypes.number.isRequired,
    message:     PropTypes.object.isRequired,
    person_name: PropTypes.string.isRequired
  };

  getMessage() {
    return {
      __html: this.props.message.message
    };
  }

  render() {
    const my = this.props.message.person === this.props.me.get('id');
    return (<Segment classes={['row', { my }]}>
      <div className="message">
        {!my ? <div className="agent name">{this.props.message.person_name}</div> : null}

        <div dangerouslySetInnerHTML={this.getMessage()}></div>
      </div>
    </Segment>);
  }
}

export default Message;
