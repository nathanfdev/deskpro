import React, { PropTypes } from 'react';
import Loader from 'react-loader';
import { SegmentsGroup } from 'DeskPRO/Component/Semantic/Segment';
import Message from './Message';


class MessageList extends React.Component {
  static propTypes = {
    me:              PropTypes.object.isRequired,
    agents:          PropTypes.object.isRequired,
    messages:        PropTypes.object.isRequired,
    searchQuery:     PropTypes.string,
    current:         PropTypes.object.isRequired,
    loadingMessages: PropTypes.bool.isRequired
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

  getPath = () => {
    let path;
    if (!this.props.searchQuery) {
      path = ['chatMessages', this.props.current.get('id')];
    } else {
      path = ['searchMessages', this.props.current.get('id')];
    }
    return path;
  };


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
    let msg = this.props.messages.hasIn(this.getPath()) ? this.props.messages.getIn(this.getPath()).messages : [];
    msg = msg.sort((first, second) => first.timestamp - second.timestamp);
    const loaded = !this.props.loadingMessages || msg.size > 0;
    return (
      <Loader loaded={loaded} parentClassName="box">
        {msg.size > 0 ? this.renderList(msg) : MessageList.renderEmpty()}
      </Loader>
    );
  }
}

export default MessageList;
