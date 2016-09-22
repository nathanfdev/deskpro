import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import { loadMessages } from '../../../Actions/messagesActions';
import MessageList from './MessageList';
import EmojiBox from './EmojiBox';

class Container extends React.Component {
  static propTypes = {
    me:              PropTypes.object.isRequired,
    agents:          PropTypes.object.isRequired,
    departments:     PropTypes.object.isRequired,
    teams:           PropTypes.object.isRequired,
    current:         PropTypes.object.isRequired,
    isOpen:          PropTypes.bool.isRequired,
    clickOut:        PropTypes.func,
    onSubmit:        PropTypes.func,
    onChange:        PropTypes.func.isRequired,
    onAttach:        PropTypes.func.isRequired,
    messages:        PropTypes.object,
    dispatch:        PropTypes.func,
    loadingMessages: PropTypes.bool.isRequired
  };

  static defaultProps = {
    clickOut() {

    },
    onSubmit() {

    }
  };

  constructor(props) {
    super(props);
    this.state = {
      emojiOpened: false
    };

    this.openEmoji    = this.openEmoji.bind(this);
    this.closeEmoji   = this.closeEmoji.bind(this);
    this.handleChange = this.handleChange.bind(this);
    this.addEmoji     = this.addEmoji.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
  }

  componentDidMount() {
    this.refresh();
  }

  getAgentHeader(chat) {
    let agentId;
    for (const id of chat.get('agents')) {
      if (id !== this.props.me.get('id')) {
        agentId = id;
        break;
      }
    }
    return this.props.agents.getIn([agentId, 'name']);
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
    console.log(emoji);
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

  render() {
    const { current, isOpen, messages, me, agents, loadingMessages } = this.props;

    return (
      <Detached
        isOpen={isOpen}
        positionTarget={document.getElementById(`chat-${current.get('id')}`)}
        positionMy="left-43 top-2"
      >
        <ClickOut onClickOut={() => this.clickOut()} ignoreNodes={['.emoji.box']}>
          <div className="ui popup left bottom im chat drawer">
            <div className="im header">{this.getHeader()}</div>
            <div className="box">
              <Scrollable vertical>
                <MessageList
                  loadingMessages={loadingMessages}
                  current={current}
                  messages={messages}
                  me={me}
                  agents={agents}
                />
              </Scrollable>
            </div>
            <div className="reply">
              <form onSubmit={this.handleSubmit}>
                <input value={this.state.message} type="text" onChange={this.handleChange} />
                <i className="fa fa-paperclip reply-icon" onClick={this.handleAttach} />
                <i className="fa fa-smile-o reply-icon emoji trigger" onClick={this.openEmoji} ref={(c) => { this.emoji = c; }} />
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
