import React, { PropTypes } from 'react';
import Loader from 'react-loader';
import { SegmentsGroup } from 'DeskPRO/Component/Semantic/Segment';
import { Header } from 'DeskPRO/Component/Semantic/Common';
import Scrollarea from './Scrollarea';
import Message from './Message';
import HeaderHelper from './HeaderHelper';

class MessageList extends React.Component {
  static propTypes = {
    me:              PropTypes.object.isRequired,
    messages:        PropTypes.object.isRequired,
    searchQuery:     PropTypes.string,
    current:         PropTypes.object.isRequired,
    loadingMessages: PropTypes.bool.isRequired,
    markNewMessages: PropTypes.func,
    agents:          PropTypes.object.isRequired,
    people:          PropTypes.object.isRequired,
    teams:           PropTypes.object.isRequired,
    departments:     PropTypes.object.isRequired,
    onScroll:        PropTypes.func,
    onAgentClick:    PropTypes.func.isRequired
  };

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
          msg.map((message) => {
            let agent = agents.get(message.person);
            if (!agent) {
              agent = people.get(message.person);
            }
            const result = (
              <Message
                key={message.id}
                agent={agent}
                searchQuery={searchQuery}
                onAgentClick={onAgentClick}
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
            {msg.size > 0 ? this.renderList(msg) : this.renderEmpty()}
          </Loader>
        </Scrollarea>
      </div>
    );
  }
}

export default MessageList;
