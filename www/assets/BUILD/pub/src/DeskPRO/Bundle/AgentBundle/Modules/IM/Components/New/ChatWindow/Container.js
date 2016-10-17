import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import classNames from 'classnames';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import { loadMessages } from '../../../Actions/messagesActions';
import MessageList from './MessageList';
import EmojiBox from './EmojiBox';

class Container extends React.Component {
  static propTypes = {
    me:                 PropTypes.object.isRequired,
    agents:             PropTypes.object.isRequired,
    departments:        PropTypes.object.isRequired,
    teams:              PropTypes.object.isRequired,
    current:            PropTypes.object.isRequired,
    isOpen:             PropTypes.bool.isRequired,
    clickOut:           PropTypes.func,
    onSubmit:           PropTypes.func,
    onChange:           PropTypes.func.isRequired,
    onAttach:           PropTypes.func.isRequired,
    messages:           PropTypes.object,
    dispatch:           PropTypes.func,
    loadingMessages:    PropTypes.bool.isRequired,
    markNewMessages:    PropTypes.func,
    openGroupDrawer:    PropTypes.func,
    onScroll:           PropTypes.func,
    shouldScrollBottom: PropTypes.bool.isRequired
  };

  static defaultProps = {
    clickOut() {

    },
    onSubmit() {

    },
    openGroupDrawer() {

    }
  };

  constructor(props) {
    super(props);
    this.state = {
      emojiOpened:       false,
      expandGroupHeader: false
    };
    this.firstScroll = true;

    this.openEmoji    = this.openEmoji.bind(this);
    this.closeEmoji   = this.closeEmoji.bind(this);
    this.handleChange = this.handleChange.bind(this);
    this.addEmoji     = this.addEmoji.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
  }

  componentDidMount() {
    this.refresh();
  }

  componentDidUpdate() {
    if (this.scrollarea && (this.firstScroll || this.props.shouldScrollBottom)) {
      this.scrollarea.scrollBottom();
      this.firstScroll = false;
    }
  }

  getAgentHeader(chat) {
    const { agents, me, openGroupDrawer } = this.props;
    let agentId;
    for (const id of chat.get('agents')) {
      if (id !== me.get('id')) {
        agentId = id;
        break;
      }
    }
    const agent =  agents.get(agentId);
    return (
      <span className="im create group" onClick={() => { openGroupDrawer([agent.get('id')]); }}>
        {agents.getIn([agentId, 'name'])}
        <i className="icon group add" />
      </span>
    );
  }

  getDepartmentHeader(chat) {
    return this.props.departments.getIn([chat.getIn(['departments', 0]), 'title']);
  }

  getAgentTeamHeader(chat) {
    return this.props.teams.getIn([chat.getIn(['agent_teams', 0]), 'name']);
  }

  getHeader() {
    const { current } = this.props;
    switch (current.get('chat_type')) {
      case 'agent':
        return this.getAgentHeader(current);
      case 'department':
        return this.getDepartmentHeader(current);
      case 'team':
        return this.getAgentTeamHeader(current);
      case 'group':
        return current.get('name');
      case 'everyone':
        return 'Everyone';
      default:
        return 'some im';
    }
  }

  refresh() {
    this.props.dispatch(loadMessages(this.props.current.get('id'), ''));
  }

  openEmoji() {
    this.setState({ emojiOpened: true });
  }

  closeEmoji() {
    this.setState({ emojiOpened: false });
  }

  clickOut() {
    this.closeEmoji();
    this.props.clickOut(this.props.current.get('id'));
  }

  addEmoji(emoji) {
    console.log(emoji, this.state.emojiOpened);
  }

  handleChange(event) {
    this.setState({ message: event.target.value });
    this.props.onChange(event.target.value);
  }

  handleSubmit() {
    this.props.onSubmit(this.state.message);
    this.setState({ message: '' });
  }

  handleAttach() {
    this.props.onAttach();
  }

  groupHeader() {
    let agents = this.props.current.get('agents');
    agents = this.state.expandGroupHeader ? agents : agents.slice(0, 9);
    if (this.props.current.get('chat_type') === 'group') {
      return (
        <Segment vertical className={classNames('group participants', { expanded: this.state.expandGroupHeader })}>
          <i
            className="write icon group-edit"
            onClick={() => this.setState({ expandGroupHeader: !this.state.expandGroupHeader })}
          />
          {agents.map(
            (agentId) => {
              if (agentId === this.props.me.get('id')) {
                return null;
              }

              const className = ['ui avatar image im'];
              const agent = this.props.agents.get(agentId);
              if (!agent.get('online')) {
                className.push('offline');
              }

              return (<PersonAvatar
                key={`agent_${agentId}`}
                person={agent}
                size={24}
                className={classNames(className)}
                color={chooseColor(agent)}
              />);
            }
          )}
          <span className="dots">
            {this.props.current.get('agents').size > 9 && !this.state.expandGroupHeader ? '...' : null}
          </span>
        </Segment>
      );
    }

    return null;
  }

  render() {
    const { current, isOpen, messages, me, agents, loadingMessages, onScroll } = this.props;

    return (
      <Detached
        isOpen={isOpen}
        positionTarget={document.getElementById(`chat-${current.get('id')}`)}
        positionMy="left-43 top-2"
      >
        <ClickOut onClickOut={() => this.clickOut()} ignoreNodes={['.emoji.box']}>
          <div className="ui popup left bottom im chat drawer">
            <div className="im header">{this.getHeader()}</div>
            {this.groupHeader()}
            <div className="box">
              <Scrollable onScroll={onScroll} vertical ref={(c) => { this.scrollarea = c; }}>
                <MessageList
                  scrollarea={this.scrollarea}
                  loadingMessages={loadingMessages}
                  current={current}
                  messages={messages}
                  me={me}
                  agents={agents}
                  markNewMessages={this.props.markNewMessages}
                />
              </Scrollable>
            </div>
            <div className="reply">
              <form onSubmit={this.handleSubmit}>
                <input value={this.state.message} type="text" onChange={this.handleChange} />
                <i className="fa fa-paperclip reply-icon" onClick={this.handleAttach} />
                <i
                  className="fa fa-smile-o reply-icon emoji trigger"
                  onClick={this.openEmoji} ref={(c) => { this.emoji = c; }}
                />
              </form>
              <EmojiBox
                isOpen={this.state.emojiOpened}
                clickOut={this.closeEmoji}
                emojiNode={this.emoji}
                emojiClick={this.addEmoji}
              />
            </div>
          </div>
        </ClickOut>
      </Detached>
    );
  }
}

export default Container;
