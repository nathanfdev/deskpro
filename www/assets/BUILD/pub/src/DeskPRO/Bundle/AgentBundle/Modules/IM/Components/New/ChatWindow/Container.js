import React, { PropTypes } from 'react';
import RteEditor from 'DeskPRO/Component/Rte/RteEditor';
import classNames from 'classnames';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import emojione from 'emojione';
import MessageList from './MessageList';
import HeaderHelper from './HeaderHelper';
import EmojiBox from './EmojiBox';

emojione.imagePathSVGSprites = `./..${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/emoticons/emojione.sprites.svg`;
emojione.imageType = 'png';
emojione.sprites = true;

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
    onChatSearch:    PropTypes.func,
    onAgentClick:    PropTypes.func.isRequired
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

    this.openEmoji    = this.openEmoji.bind(this);
    this.closeEmoji   = this.closeEmoji.bind(this);
    this.handleChange = this.handleChange.bind(this);
    this.addEmoji     = this.addEmoji.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
  }

  componentDidMount() {
    this.refresh(this.props);
  }

  componentWillReceiveProps(props) {
    if (props.current.get('id') !== this.props.current.get('id')) {
      this.refresh(props);
    }
  }

  getPath = (props) => {
    let path;
    if (!props.searchQuery) {
      path = ['chatMessages', props.current.get('id')];
    } else {
      path = ['searchMessages', props.current.get('id')];
    }
    return path;
  };

  getHeader() {
    const { agents, teams, departments, current, me, openGroupDrawer } = this.props;
    const props = { agents, teams, departments, current, me, openGroupDrawer };

    if (!this.headerHelper) {
      this.headerHelper = new HeaderHelper(props);
    } else {
      this.headerHelper.setProps(props);
    }

    const header = this.headerHelper.getHeaderText();

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

  toggleSearch() {
    if (this.state.searching) {
      this.props.onChatSearch('');
    }
    this.setState({ searching: !this.state.searching, expandGroupHeader: false });
  }

  refresh(props) {
    if (
      !props.messages.hasIn(this.getPath(props))
      || (props.messages.hasIn(this.getPath(props)) && props.messages.getIn(this.getPath(props)).messages.size < 1)
      || (props.messages.hasIn(this.getPath(props)) && !props.messages.getIn(this.getPath(props)).page)
    ) {
      this.props.loadMessages();
    }
    this.setState({ mounted: true });
  }

  openEmoji() {
    this.setState({ emojiOpened: true });
  }

  closeEmoji() {
    this.setState({ emojiOpened: false });
  }

  clickOut() {
    this.closeEmoji();
    this.setState({ searching: false, expandedHeader: false });
    this.props.onChatSearch('');
    this.props.clickOut(this.props.current.get('id'));
  }

  addEmoji(emoji) {
    emojione.imageType = 'png';
    emojione.sprites = false;

    const editor = this.editor;
    const medium = editor.getMediumEditor();
    medium.stopSelectionUpdates();

    // focus the rte field
    editor.focus();

    const contentWindow = medium.options.contentWindow;
    const ownerDocument = medium.options.ownerDocument;

    const html = emoji.shortname;

    if (contentWindow.getSelection) {
      // IE9 and non-IE
      const selection = contentWindow.getSelection();
      if (selection.getRangeAt && selection.rangeCount) {
        let range = selection.getRangeAt(0);
        range.deleteContents();

        // Range.createContextualFragment() would be useful here but is
        // only relatively recently standardized and is not supported in
        // some browsers (IE9, for one)
        const el     = document.createElement('div');
        el.innerHTML = html;
        const frag   = document.createDocumentFragment();

        let node;
        let lastNode;

        do {
          node = el.firstChild;
          if (node) {
            lastNode = frag.appendChild(node);
          }
        } while (node);

        range.insertNode(frag);

        // Preserve the selection
        if (lastNode) {
          range = ownerDocument.createRange();
          range.selectNodeContents(lastNode);
          range.collapse(false);

          selection.removeAllRanges();
          selection.addRange(range);
        }
      }
    } else if (ownerDocument.selection && ownerDocument.selection.type !== 'Control') {
      // IE < 9
      ownerDocument.selection.createRange().pasteHTML(html);
    }

    medium.saveSelection();
    medium.trigger('onChange');

    // focus the rte again to correct display caret position
    editor.focus();
  }

  handleChange(text) {
    this.setState({ message: text });
    this.props.onChange(text);
  }

  handleSubmit(event) {
    event.preventDefault();
    this.props.onSubmit(this.state.message);
    this.setState({ message: '' });
  }

  handleAttach() {
    this.props.onAttach();
  }

  groupHeader() {
    const { current, agents, onAgentClick, openGroupDrawer, me } = this.props;
    const { expandGroupHeader, searching } = this.state;

    let localAgents = current.get('agents');
    localAgents = expandGroupHeader ? localAgents : localAgents.slice(0, 9);

    if (current.get('chat_type') === 'group' && !searching) {
      return (
        <Segment vertical className={classNames('group participants', { expanded: expandGroupHeader })}>
          <span className="control">
            <i className="fa fa-times" onClick={() => this.setState({ expandGroupHeader: false })} />
            <i
              className="write icon group-edit"
              onClick={
                () => {
                  openGroupDrawer(
                    current.get('agents').filter(item => item !== me.get('id')).toJS(),
                    current
                  );
                }
              }
            />
          </span>
          {localAgents.map(
            (agentId) => {
              if (agentId === me.get('id')) {
                return null;
              }

              const className = ['ui avatar image im'];
              const agent = agents.get(agentId);
              if (!agent.get('online')) {
                className.push('offline');
              }

              return (
                <span key={`agent_span_${agentId}`} onClick={() => onAgentClick(agentId, 'agent')}>
                  <PersonAvatar
                    key={`agent_${agentId}`}
                    person={agent}
                    size={24}
                    className={classNames(className)}
                    color={chooseColor(agent)}
                  />
                </span>
              );
            }
          )}
          <span className="dots" onClick={() => this.setState({ expandGroupHeader: true })}>
            {current.get('agents').size > 9 && !expandGroupHeader ? '...' : null}
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
    const { isOpen, loadingMessages, onScroll, markNewMessages, searchQuery, onAgentClick } = this.props;
    const { current, messages, me, agents, teams, departments } = this.props;

    return (
      <Detached
        zIndex={99999}
        isOpen={isOpen}
        positionTarget={document.getElementById(`chat-${current.get('id')}`)}
        positionMy="left-43 top-2"
      >
        <ClickOut onClickOut={() => this.clickOut()} ignoreNodes={['.emoji.box', '.im.recent .im.wrapper']}>
          <div className="ui popup left bottom im chat drawer">
            <div className="im header">{this.getHeader()}</div>
            {this.searchHeader()}
            {this.groupHeader()}
            <div className="box">
              <MessageList
                loadingMessages={loadingMessages}
                current={current}
                messages={messages}
                me={me}
                agents={agents}
                temas={teams}
                departments={departments}
                searchQuery={searchQuery}
                markNewMessages={markNewMessages}
                onScroll={onScroll}
                onAgentClick={onAgentClick}
              />
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
              {this.emoji ?
                <EmojiBox
                  isOpen={this.state.emojiOpened}
                  clickOut={this.closeEmoji}
                  emojiNode={this.emoji}
                  emojiClick={this.addEmoji}
                />
                : null
              }
            </div>
          </div>
        </ClickOut>
      </Detached>
    );
  }
}

export default Container;
