import React, { PropTypes } from 'react';
import { SegmentsGroup } from 'DeskPRO/Component/Semantic/Segment';
import Message from './Message';

class MessageList extends React.Component {
  static propTypes = {
    me:       PropTypes.object.isRequired,
    agents:   PropTypes.object.isRequired,
    messages: PropTypes.object.isRequired
  };

  static renderEmpty() {
    return (
      <ul className="chat-message-list">
        <li className="chat-controls">
          <a>Sorry, nothing found here</a>
        </li>
      </ul>
    );
  }

  renderList(msg) {
    let previous = false;
    return (
      <SegmentsGroup vertical>
        {
          msg.map((message, index) => {
            const result = (
              <Message
                key={index}
                message={message}
                size={msg.size}
                current={parseInt(index, 10)}
                previousMessage={previous}
                agents={this.props.agents}
                me={this.props.me}
              />
            );
            previous = message;
            return result;
          })
        }
      </SegmentsGroup>
    );
  }

  render() {
    const msg = this.props.messages.sort((first, second) => first.timestamp - second.timestamp);
    return msg.size > 0 ? this.renderList(msg) : MessageList.renderEmpty();
  }
}

export default MessageList;
