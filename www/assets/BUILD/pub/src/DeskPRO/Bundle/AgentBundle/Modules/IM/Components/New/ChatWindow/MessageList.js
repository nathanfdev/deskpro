import React, { PropTypes } from 'react';
import Loader from 'react-loader';
import { SegmentsGroup } from 'DeskPRO/Component/Semantic/Segment';
import { Header } from 'DeskPRO/Component/Semantic/Common';
import Message from './Message';

class MessageList extends React.Component {
  static propTypes = {
    me:              PropTypes.object.isRequired,
    messages:        PropTypes.object.isRequired,
    searchQuery:     PropTypes.string,
    current:         PropTypes.object.isRequired,
    loadingMessages: PropTypes.bool.isRequired,
    markNewMessages: PropTypes.func
  };

  static renderEmpty() {
    return (
      <Header
        className="empty"
        level={5}
        content="Sorry, nothing found here"
      />
    );
  }

  componentDidMount() {
    this.props.markNewMessages();
  }

  componentDidUpdate() {
    this.props.markNewMessages();
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
          this.props.loadingMessages
            ? <div style={{ height: '20px', width: '100%', position: 'relative' }}>
              <Loader loaded={!this.props.loadingMessages} opacity={0} width={4} scale={0.5} color="#4696dc" />
            </div>
            : null
        }
        {
          msg.map((message, index) => {
            const result = (
              <Message
                key={index}
                message={message}
                previous={previous}
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
    const path     = this.getPath();
    let msg = this.props.messages.hasIn(path) ? this.props.messages.getIn(path).messages : [];
    msg          = msg.sort((first, second) => first.timestamp - second.timestamp);
    const loaded = !this.props.loadingMessages || msg.size > 0;
    return (
      <Loader loaded={loaded} parentClassName="box">
        {msg.size > 0 ? this.renderList(msg) : MessageList.renderEmpty()}
      </Loader>
    );
  }
}

export default MessageList;
