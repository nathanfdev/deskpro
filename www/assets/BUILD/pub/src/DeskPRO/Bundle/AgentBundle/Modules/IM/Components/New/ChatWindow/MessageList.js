import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import Loader from 'react-loader';
import { SegmentsGroup } from 'DeskPRO/Component/Semantic/Segment';
import { Header } from 'DeskPRO/Component/Semantic/Common';
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
    this.messageToScrollId = null;
    this.messages = {};
  }


  componentDidMount() {
    this.props.markNewMessages();
    this.shouldScrollBottom = false;
    this.firstScroll = true;
  }

  componentWillReceiveProps(props) {
    if (this.props.current.get('id') !== props.current.get('id')) {
      this.firstScroll = true;
    }

    this.props = props;
  }

  componentWillUpdate() {
    const node = this.scrollBox;
    this.shouldScrollBottom = node && (node.scrollTop + node.offsetHeight === node.scrollHeight);
  }

  componentDidUpdate() {
    const { loadingMessages, markNewMessages } = this.props;
    if (loadingMessages) return;
    if (this.firstScroll) {
      this.scroll();
      this.firstScroll = false;
    }
    if (this.shouldScrollBottom) {
      this.scroll();
    }
    if (this.messageToScrollId) {
      const message = this.messages[this.messageToScrollId];
      if (message && !message.isSearchResult() && message.getNode()) {
        const messageNodeRect = message.getNode().getBoundingClientRect();
        const chatContextRect = this.scrollBox.getBoundingClientRect();
        const deltaY = chatContextRect.bottom - messageNodeRect.top;
        this.messageToScrollId = false;
        this.scrollYTo(deltaY);
      }
    }

    if (this.moveScroll) {
      this.scrollYTo(this.oldHeight); // don't scroll to top when new batch of messages loaded
      this.moveScroll = false;
    }
    this.oldHeight = this.scrollBox.scrollHeight; // update current height
    markNewMessages();
  }

  onSearchMessageClick = (message) => {
    this.messageToScrollId = message.getMessageObject().id;
    this.props.onSearchMessageClick(message.getMessageObject());
  };

  onScroll = () => {
    this.moveScroll = this.props.onScroll({
      atTheTop:        this.scrollBox.scrollTop === 0 && !this.firstScroll,
      containerHeight: this.scrollBox.scrollHeight }
    );
  };

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

  scroll = () => {
    if (this.scrollBox) {
      this.scrollBox.scrollTop = this.scrollBox.scrollHeight;
    }
  };

  scrollStub = () => {
    this.scroll();
  };

  scrollYTo = (deltaY) => {
    if (this.scrollBox) {
      this.scrollBox.scrollTop = this.scrollBox.scrollHeight - deltaY;
    }
  };

  renderEmpty() {
    const { agents, people, teams, departments, current, me, searchQuery } = this.props;
    const props = { agents, people, teams, departments, current, me };

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
    let isAgent = true;
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
              let person = agents.get(message.person);
              if (!person) {
                person = people.get(message.person);
                isAgent = false;
              }
              result.push(
                <Message
                  ref={(x) => { this.messages[message.id] = x; }}
                  id={`chat-${message.chat}-message-${message.id}`}
                  key={message.id}
                  person={person}
                  isAgent={isAgent}
                  searchQuery={searchQuery}
                  onSearchMessageClick={this.onSearchMessageClick}
                  onAgentClick={onAgentClick}
                  message={message}
                  previous={previous}
                  me={me}
                  scrollStub={this.scrollStub}
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
    const { messages, loadingMessages } = this.props;
    const msg = Immutable.OrderedMap(messages.hasIn(path) ? messages.getIn(path).messages : []);
    const loaded = !loadingMessages || msg.size > 0;

    return (
      <div
        onScroll={this.onScroll}
        ref={(c) => { this.scrollBox = c; }}
        className="box"
        id={`chat-container-${this.props.current.get('id')}`}
      >
        <Loader loaded={loaded} parentClassName="loader">
          {msg.size > 0 ? this.renderList(msg) : this.renderEmpty()}
        </Loader>
      </div>
    );
  }
}

export default MessageList;
