import React, { PropTypes } from 'react';
import Immutable from 'immutable';
import Loader from 'react-loader';
import { SegmentsGroup } from 'DeskPRO/Component/Semantic/Segment';
import { Header } from 'DeskPRO/Component/Semantic/Common';
import Scrollarea from './Scrollarea';
import Message from './Message';
import HeaderHelper from './HeaderHelper';

class MessageList extends React.Component {
  static propTypes = {
    me:                   PropTypes.object.isRequired,
    messages:             PropTypes.object.isRequired,
    searchQuery:          PropTypes.string,
    current:              PropTypes.object.isRequired,
    loadingMessages:      PropTypes.bool.isRequired,
    markNewMessages:      PropTypes.func,
    agents:               PropTypes.object.isRequired,
    people:               PropTypes.object.isRequired,
    teams:                PropTypes.object.isRequired,
    departments:          PropTypes.object.isRequired,
    onScroll:             PropTypes.func,
    onAgentClick:         PropTypes.func.isRequired,
    onSearchMessageClick: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.firstScroll = null;
    this.messageToScrollId = null;
    this.messages = {};
    this.onSearchMessageClick = this.onSearchMessageClick.bind(this);
    this.onScrollToMessage = this.onScrollToMessage.bind(this);
  }

  componentDidMount() {
    this.props.markNewMessages();
  }

  componentDidUpdate() {
    const { loadingMessages, markNewMessages } = this.props;
    if (loadingMessages) return;
    markNewMessages();
  }

  onSearchMessageClick(message) {
    this.messageToScrollId = message.getMessageObject().id;
    this.props.onSearchMessageClick(message.getMessageObject());
  }

  onScrollToMessage = () => {
    this.messageToScrollId = null;
  };

  getPath = () => {
    let path;
    const { searchQuery, current } = this.props;
    if (!searchQuery) {
      path = ['chatMessages', current.get('id')];
    } else {
      path = ['searchMessages', current.get('id')];
    }
    return path;
  };

  renderEmpty() {
    const { agents, teams, departments, current, me, searchQuery } = this.props;
    const props = { agents, teams, departments, current, me };

    if (!this.headerHelper) {
      this.headerHelper = new HeaderHelper(props);
    } else {
      this.headerHelper.setProps(props);
    }

    const header = this.headerHelper.getEmptyHeader();

    return (
      <Header
        className="empty"
        level={5}
        content={!searchQuery ? `Send your first message to ${header}!` : 'No matching results'}
      />
    );
  }

  renderList(msg) {
    let previous = false;
    const { agents, people, searchQuery, me, onAgentClick } = this.props;
    return (
      <SegmentsGroup vertical>
        {
          msg.sort(
            (first, second) => {
              if (first.timestamp === second.timestamp) {
                return first.id - second.id;
              }
              return first.timestamp - second.timestamp;
            }).map((message) => {
              const result = [];
              let agent = agents.get(message.person);
              if (!agent) {
                agent = people.get(message.person);
              }
              result.push(
                <Message
                  ref={(x) => { this.messages[message.id] = x; }}
                  id={`chat-${message.chat}-message-${message.id}`}
                  key={message.id}
                  agent={agent}
                  searchQuery={searchQuery}
                  onSearchMessageClick={this.onSearchMessageClick}
                  onAgentClick={onAgentClick}
                  message={message}
                  previous={previous}
                  me={me}
                />
              );
              if (previous && message.page !== previous.page) {
                let firstPage;
                let secondPage;
                if (previous.page - 1 > message.page + 1) {
                  firstPage = previous.page - 1;
                  secondPage = message.page + 1;
                } else {
                  firstPage = message.page + 1;
                  secondPage = previous.page - 1;
                }
                result.push(<span className="chatDivider" id={`chat-${message.chat}-page-${firstPage}`} data-page={firstPage} />);
                result.push(<span className="chatDivider" id={`chat-${message.chat}-page-${secondPage}`} data-page={secondPage} />);
              }
              previous = message;
              return result;
            })
        }
        <span className="chatDivider" id={`chat-${msg.first().chat}-page-1`} data-page={1} />
      </SegmentsGroup>
    );
  }

  render() {
    const path     = this.getPath();
    const { onScroll, messages, loadingMessages } = this.props;
    const msg = Immutable.OrderedMap(messages.hasIn(path) ? messages.getIn(path).messages : []);
    const loaded = !loadingMessages || msg.size > 0;

    return (
      <div
        className="dp-scrollable as-js-scrollbar as-vertical"
        id={`chat-container-${this.props.current.get('id')}`}
      >
        <Scrollarea
          className="dpscrollarea"
          contentClassName="dpscrollarea"
          vertical
          onScroll={onScroll}
          onScrollToMessage={this.onScrollToMessage}
          messageToScroll={!loadingMessages ? this.messages[this.messageToScrollId] : null}
          ref={(c) => { this.scrollarea = c; }}
        >
          <Loader loaded={loaded} parentClassName="box">
            {msg.size > 0 ? this.renderList(msg) : this.renderEmpty()}
          </Loader>
        </Scrollarea>
      </div>
    );
  }
}

export default MessageList;
