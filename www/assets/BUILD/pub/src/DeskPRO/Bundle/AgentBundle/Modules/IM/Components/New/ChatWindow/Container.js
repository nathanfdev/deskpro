import React, { PropTypes } from 'react';
import { RteEditor } from 'DeskPRO/Component/Rte/RteEditor';
import classNames from 'classnames';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import MessageList from './MessageList';
import EmojiBox from './EmojiBox';

class Container extends React.Component {
  static propTypes = {
    me:              PropTypes.object.isRequired,
    agents:          PropTypes.object.isRequired,
    departments:     PropTypes.object.isRequired,
    teams:           PropTypes.object.isRequired,
    current:         PropTypes.object.isRequired,
    searchQuery:     PropTypes.string,
    isOpen:          PropTypes.bool.isRequired,
    clickOut:        PropTypes.func,
    onSubmit:        PropTypes.func,
    onChange:        PropTypes.func,
    onAttach:        PropTypes.func,
    messages:        PropTypes.object,
    loadMessages:    PropTypes.func,
    loadingMessages: PropTypes.bool.isRequired,
    markNewMessages: PropTypes.func,
    openGroupDrawer: PropTypes.func,
    onScroll:        PropTypes.func,
    onChatSearch:    PropTypes.func
  };

  static defaultProps = {
    searchQuery: '',
    onChange() {

    },
    onAttach() {

    },
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
      expandGroupHeader: false,
      searching:         false
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
    if (this.props.loadingMessages) return;
    if (this.scrollarea) {
      if (!this.props.loadingMessages && this.firstScroll) {
        this.scrollarea.scrollBottom();
        this.firstScroll = false;

        return;
      }
    }

    return;
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
    return [
      agents.getIn([agentId, 'name']),
      <i className="icon group add" onClick={() => { openGroupDrawer([agent.get('id')]); }} />
    ];
  }

  getDepartmentHeader(chat) {
    return this.props.departments.getIn([chat.getIn(['departments', 0]), 'title']);
  }

  getAgentTeamHeader(chat) {
    return this.props.teams.getIn([chat.getIn(['agent_teams', 0]), 'name']);
  }

  getHeader() {
    const { current } = this.props;
    let header;
    switch (current.get('chat_type')) {
      case 'agent':
        header = this.getAgentHeader(current);
        break;
      case 'department':
        header = this.getDepartmentHeader(current);
        break;
      case 'team':
        header = this.getAgentTeamHeader(current);
        break;
      case 'group':
        header = current.get('name');
        break;
      case 'everyone':
        header = 'Everyone';
        break;
      default:
        header = 'some im';
        break;
    }
    return (
      <span className="wrapper">
        {header}
        <i
          className={classNames('search icon', { enabled: this.state.searching })}
          onClick={() => { this.toggleSearch(); }}
        />
      </span>
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

  toggleSearch() {
    if (this.state.searching) {
      this.props.onChatSearch('');
    }
    this.setState({ searching: !this.state.searching, expandedHeader: false });
  }

  refresh() {
    this.props.loadMessages();
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

  handleChange(text) {
    this.setState({ message: text });
    this.props.onChange(text);
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
    if (this.props.current.get('chat_type') === 'group' && !this.state.searching) {
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

  searchHeader() {
    if (this.state.searching) {
      return (
        <Segment vertical className="search">
          <SearchBox
            onFocus={() => { window.DeskPRO_Window.keyboardShortcuts.isPaused = true; }}
            onBlur={() => { window.DeskPRO_Window.keyboardShortcuts.isPaused = true; }}
            onUserInput={this.props.onChatSearch}
          />
        </Segment>
      );
    }

    return null;
  }

  render() {
    const { current, isOpen, messages, me, agents, loadingMessages, onScroll, searchQuery } = this.props;

    return (
      <Detached
        isOpen={isOpen}
        positionTarget={document.getElementById(`chat-${current.get('id')}`)}
        positionMy="left-43 top-2"
      >
        <ClickOut onClickOut={() => this.clickOut()} ignoreNodes={['.emoji.box']}>
          <div className="ui popup left bottom im chat drawer">
            <div className="im header">{this.getHeader()}</div>
            {this.searchHeader()}
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
                  searchQuery={searchQuery}
                  markNewMessages={this.props.markNewMessages}
                />
              </Scrollable>
            </div>
            <div className="reply">
              <form onSubmit={this.handleSubmit}>
                <RteEditor
                  inline
                  ref={(c) => { this.editor = c; }}
                  value={this.state.message}
                  onChange={this.handleChange}
                  onSubmit={this.handleSubmit}
                  onFocus={() => { window.DeskPRO_Window.keyboardShortcuts.isPaused = true; }}
                  onBlur={() => { window.DeskPRO_Window.keyboardShortcuts.isPaused = false; }}
                  className="textarea"
                  options={{
                    autoLink:      true,
                    imageDragging: true,
                    placeholder:   false,
                    toolbar:       {
                      buttons:                ['bold', 'italic', 'underline'],
                      updateOnEmptySelection: true
                    },
                    paste: {
                      forcePlainText:  false,
                      cleanPastedHTML: false,
                      cleanAttrs:      ['style', 'dir']
                    }
                  }}
                />
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
