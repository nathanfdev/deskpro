import React, { PropTypes } from 'react';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';

class Message extends React.Component
{
  static propTypes = {
    me:           PropTypes.object.isRequired,
    uuid:         PropTypes.string.isRequired,
    id:           PropTypes.number.isRequired,
    message:      PropTypes.string.isRequired,
    person_name:  PropTypes.string.isRequired,
    person:       PropTypes.number.isRequired,
    status:       PropTypes.number.isRequired,
    timestamp:    PropTypes.number.isRequired,
    date_created: PropTypes.string.isRequired
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
