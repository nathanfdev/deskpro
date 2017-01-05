import React, { PropTypes } from 'react';
import Loader from 'react-loader';
import { SegmentsGroup } from 'DeskPRO/Component/Semantic/Segment';
import { Header } from 'DeskPRO/Component/Semantic/Common';
import Scrollarea from './Scrollarea';
import Message from './Message';

class MessageList extends React.Component {
  static propTypes = {
    me:              PropTypes.object.isRequired,
    messages:        PropTypes.object.isRequired,
    searchQuery:     PropTypes.string,
    current:         PropTypes.object.isRequired,
    loadingMessages: PropTypes.bool.isRequired,
    markNewMessages: PropTypes.func,
    agents:          PropTypes.object.isRequired,
    onScroll:        PropTypes.func
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

  constructor(props) {
    super(props);
    this.firstScroll = null;
  }

  componentDidMount() {
    this.props.markNewMessages();
  }

  componentDidUpdate() {
    const { loadingMessages, markNewMessages } = this.props;
    if (loadingMessages) return;
    markNewMessages();
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
    const { agents, searchQuery, me } = this.props;
    return (
      <SegmentsGroup vertical>
        {
          msg.map((message, index) => {
            const agent = agents.get(message.person);
            const result = (
              <Message
                key={index}
                agent={agent}
                searchQuery={searchQuery}
                message={message}
                previous={previous}
                me={me}
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
    const { onScroll, messages, loadingMessages } = this.props;
    let msg = messages.hasIn(path) ? messages.getIn(path).messages : [];
    msg     = msg.sort((first, second) => first.timestamp - second.timestamp);

    const loaded = !loadingMessages || msg.size > 0;


    return (
      <div className="dp-scrollable as-js-scrollbar as-vertical">
        <Scrollarea
          className="dpscrollarea"
          contentClassName="dpscrollarea"
          vertical
          onScroll={onScroll}
        >
          <Loader loaded={loaded} parentClassName="box">
            {msg.size > 0 ? this.renderList(msg) : MessageList.renderEmpty()}
          </Loader>
        </Scrollarea>
      </div>
    );
  }
}

export default MessageList;
