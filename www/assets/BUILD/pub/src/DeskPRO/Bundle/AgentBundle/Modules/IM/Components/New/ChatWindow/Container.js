import 'froala-editor/js/froala_editor.pkgd.min';
import PropTypes from 'prop-types';
import React from 'react';
import ReactTooltip from 'react-tooltip';
import Isvg from 'react-inlinesvg';
import $ from 'jquery';
import FroalaEditor from 'react-froala-wysiwyg';
import classNames from 'classnames';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import { List } from 'DeskPRO/Component/Semantic/List';
import { Header } from 'DeskPRO/Component/Semantic/Common';
import emojione from 'emojione';
import MessageList from './MessageList';
import HeaderHelper from './HeaderHelper';
import EmojiBox from './EmojiBox';
import AvatarHelper from '../IMTabs/AvatarHelper';

emojione.imagePathSVGSprites = `./..${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/emoticons/emojione.sprites.svg`;
emojione.imageType = 'svg';
emojione.sprites = true;

class Container extends React.Component {
  static propTypes = {
    me:                   PropTypes.object.isRequired,
    agents:               PropTypes.object.isRequired,
    people:               PropTypes.object.isRequired,
    departments:          PropTypes.object.isRequired,
    teams:                PropTypes.object.isRequired,
    current:              PropTypes.object.isRequired,
    searchQuery:          PropTypes.string,
    isOpen:               PropTypes.bool.isRequired,
    onClose:              PropTypes.func,
    saveDraft:            PropTypes.func,
    onSubmit:             PropTypes.func,
    onChange:             PropTypes.func,
    activeTabs:           PropTypes.object,
    messages:             PropTypes.object,
    loadMessages:         PropTypes.func,
    loadingMessages:      PropTypes.bool.isRequired,
    markNewMessages:      PropTypes.func,
    openGroupDrawer:      PropTypes.func,
    onScroll:             PropTypes.func,
    onChatSearch:         PropTypes.func,
    onAgentClick:         PropTypes.func.isRequired,
    onSearchMessageClick: PropTypes.func.isRequired
  };

  static defaultProps = {
    searchQuery: '',
    onChange() {

    },
    onAttach() {

    },
    onClose() {

    },
    onSubmit() {

    },
    openGroupDrawer() {

    },
    saveDraft() {

    }
  };

  static pickIcon(item) {
    switch (item.tabType) {
      case 'ticket':
        return 'mail outline';
      case 'person':
        return 'user';
      case 'article':
        return 'file text outline';
      case 'news':
        return 'newspaper';
      case 'organization':
        return 'building outline';
      case 'download':
        return 'download';
      case 'feedback':
        return 'thumbs outline up';
      case 'userchat':
        return 'comment outline';
      case 'topics':
        return 'book outline';
      default:
        return '';
    }
  }

  static onBlur() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
  }

  static onFocus() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
  }

  constructor(props) {
    super(props);
    this.state = {
      emojiOpened:       false,
      expandGroupHeader: false,
      searching:         false,
      message:           '',
      linkDrawerOpened:  false,
      editorControls:    null
    };

    this.destroyEditor        = () => {};
  }

  componentWillMount() {
    window.addEventListener('keyup', this.onEscape);
  }

  componentDidMount() {
    this.refresh(this.props);
  }

  componentWillReceiveProps(props) {
    if (props.current.get('id') !== this.props.current.get('id')) {
      this.props.saveDraft(this.props.current.get('id'), this.state.message);
      this.refresh(props);
    }
  }

  componentWillUnmount() {
    if (this.state.editorControls) { // there is a chance that component was mounted and immediately unmounted
      this.state.editorControls.destroy();
    }
    window.addEventListener('keyup', this.onEscape);
  }

  onSearchMessageClick = (message) => {
    this.setState({ searching: false }, () => {
      this.props.onChatSearch('');
      this.props.onSearchMessageClick(message);
    });
  };

  onEscape = (e) => {
    if (e.keyCode === 27 && this.props.isOpen) {
      this.closeContainer();
    }
  };

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
    const { agents, people, teams, departments, current, me, openGroupDrawer } = this.props;
    const props = { agents, people, teams, departments, current, me, openGroupDrawer };

    if (!this.headerHelper) {
      this.headerHelper = new HeaderHelper(props);
    } else {
      this.headerHelper.setProps(props);
    }

    const header = this.headerHelper.getHeaderText(true);
    const tooltipId = `tooltip-for-chat-header-${current.get('id')}`;

    return (
      <span className="wrapper">
        <span data-for={tooltipId} data-tip={header} className="chat-header dont-break-out">{header}</span>
        <ReactTooltip delayShow={1000} id={tooltipId} effect="solid" place="top" className="im-tooltip" />
        <CloseChat onClick={() => { this.closeContainer(); }} />
        <i
          className={classNames('search icon button', { enabled: this.state.searching })}
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
    const newState = { mounted: true, searching: false, expandedHeader: false };
    if (props.drafts && props.drafts[props.current.get('id')]) {
      newState.message = props.drafts[props.current.get('id')];
    } else {
      newState.message = '';
    }

    this.setState(newState);
    this.props.onChatSearch('');
  }

  openEmoji = () => {
    this.setState({ emojiOpened: true });
  };

  closeEmoji = () => {
    this.setState({ emojiOpened: false });
  };

  closeContainer() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
    this.closeEmoji();
    this.setState({ searching: false, expandedHeader: false });
    this.props.onChatSearch('');
    this.props.onClose(this.props.current.get('id'));
  }

  addEmoji = (emoji) => {
    const editor = this.editor;
    const html = emoji.shortname;
    editor.events.focus();
    editor.selection.restore();
    editor.html.insert(html);
    this.handleChange(editor.html.get());
    this.closeEmoji();
  };

  handleChange = (text) => {
    this.setState({ message: text });
    this.props.onChange(text);
  };

  handleSubmit = (event) => {
    if (!this.editor.core.isEmpty()) {
      event.preventDefault();
      this.props.onSubmit(this.state.message);
      this.props.saveDraft(this.props.current.get('id'), '');
      this.setState({ message: '' });
    }
  };

  initFroala = (initControls) => {
    this.setState({ editorControls: initControls });
    initControls.initialize();
  };

  bindFroalaEvents = (e, editor) => {
    this.editor = editor;
    editor.events.on('keydown', this.handleKeydown, true);
    if (document.activeElement.tagName.toLowerCase() === 'body') {
      window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
      editor.events.focus();
    }
  };

  handleKeydown = (e) => {
    if (e.keyCode === 13 && !(e.ctrlKey || e.metaKey || e.shiftKey || e.altKey)) {
      e.stopPropagation();
      this.handleSubmit(e);
      return false;
    }
    return e;
  };

  openLink = () => {
    this.setState({ linkDrawerOpened: true });
  };

  closeLink = () => {
    this.setState({ linkDrawerOpened: false });
  };

  openAttach = () => {
    const popupHeight   = 150;
    const maxAllowedTop = window.innerHeight - popupHeight;
    const replyFormRect = $('#replyForm')[0].getBoundingClientRect();
    // popup `top` property relative to #replyForm container
    let   top           = 25;

    if ((replyFormRect.top + top) > maxAllowedTop) {
      top = maxAllowedTop - replyFormRect.top;
    }

    this.editor.commands.exec('insertFile');
    this.state.editorControls.getEditor()('popups.setContainer', 'file.insert', $('#replyForm'));
    this.state.editorControls.getEditor()('popups.get', 'file.insert').css({ top: `${top}px`, left: '325px' });
  };

  groupHeader() {
    const { current, agents, onAgentClick, openGroupDrawer, me } = this.props;
    const { expandGroupHeader, searching } = this.state;

    let localAgents = current.get('agents').filter(item => agents.get(item));
    localAgents = expandGroupHeader ? localAgents : localAgents.slice(0, 9);

    if (current.get('chat_type') === 'group' && !searching) {
      return (
        <Segment vertical className={classNames('group participants', { expanded: expandGroupHeader })}>
          <span className="control">
            <i className="fa fa-times" onClick={() => this.setState({ expandGroupHeader: false })} />
            { current.get('admin') === me.get('id') ? (<i
              className="write icon group-edit"
              onClick={
                () => {
                  openGroupDrawer(
                    current.get('agents').filter(item => item !== me.get('id')).toJS(),
                    current
                  );
                }
              }
            />) : null }
          </span>
          {localAgents.map(
            (agentId) => {
              if (agentId === me.get('id')) {
                return null;
              }

              const className = [];
              const agent = agents.get(agentId);

              if (!agent) {
                return null;
              }

              if (!agent.get('online')) {
                className.push('offline');
              }

              return (
                <span key={`agent_span_${agentId}`} onClick={() => onAgentClick(agentId, 'agent')}>
                  {AvatarHelper.renderAgentAvatar(agent, 24, className, agent.get('name'))}
                </span>
              );
            }
          )}
          <span className="dots" onClick={() => this.setState({ expandGroupHeader: true })}>
            {current.get('agents').filter(item => agents.get(item)).size > 9 && !expandGroupHeader ? '...' : null}
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
            onFocus={Container.onFocus}
            onBlur={Container.onBlur}
            onUserInput={this.props.onChatSearch}
          />
        </Segment>
      );
    }

    return null;
  }

  handleLink(item) {
    const propertyName = item.tabType === 'userchat' ? 'conversation_id' : `${item.tabType}_id`;
    const message      = `{{${item.tabType.charAt(0).toLowerCase()}-${item.page.meta[propertyName]}}}: ${item.title}`;
    const editor       = this.editor;
    editor.events.focus();
    editor.selection.restore();
    editor.html.insert(message);
    this.handleChange(editor.html.get());
    this.closeLink();
  }

  renderAttachList() {
    const { activeTabs } = this.props;

    const items = Object.keys(activeTabs).map((index) => {
      const item = activeTabs[index];
      return {
        label:   item.title.length > 50 ? `${item.title.substr(0, 50)}\u2026` : item.title,
        icon:    Container.pickIcon(item),
        onClick: () => this.handleLink(item)
      };
    });

    return this.linkTrigger ?
      (<Detached zIndex={99999} isOpen={this.state.linkDrawerOpened} positionTarget={this.linkTrigger} positionMy="right+25 top+35">
        <ClickOut onClickOut={this.closeLink}>
          <div className="attach-list">
            <Header content="Current tabs" level={4} className="attach-header" />
            <List elements={items} />
          </div>
        </ClickOut>
      </Detached>)
      : null;
  }

  render() {
    const { isOpen, loadingMessages, onScroll, searchQuery } = this.props;
    const { markNewMessages, onAgentClick } = this.props;
    const { current, messages, me, agents, teams, departments, people } = this.props;
    const header = this.getHeader();
    let enabled = true;
    if (current.get('chat_type') === 'agent') {
      const agentId = this.headerHelper.getAgentId(current);
      if (agentId && !agents.get(agentId)) {
        enabled = false;
      }
    }

    const buttons = [
      'bold', 'italic', 'underline', 'strikeThrough', 'color',
      '-',
      'align', 'formatOL', 'formatUL', 'insertImage',
      '-',
      'insertLink', 'insertFile', 'insertVideo', 'undo', 'redo'
    ];

    const froalaConfig = {
      imageUploadMethod:         'POST',
      imageUploadParams:         { _rt: window.DP_REQUEST_TOKEN, json: true },
      imageUploadURL:            `${BASE_URL}agent/misc/accept-redactor-image-upload`, // eslint-disable-line no-undef
      imageDefaultWidth:         0,
      fileUploadMethod:          'POST',
      fileUploadParams:          { _rt: window.DP_REQUEST_TOKEN, json: true },
      fileUploadURL:             `${BASE_URL}agent/misc/accept-redactor-file-upload`, // eslint-disable-line no-undef
      videoUploadMethod:         'POST',
      videoUploadParams:         { _rt: window.DP_REQUEST_TOKEN, json: true },
      videoUploadURL:            `${BASE_URL}agent/misc/accept-redactor-file-upload`, // eslint-disable-line no-undef
      videoDefaultWidth:         0,
      videoResize:               false,
      videoDefaultDisplay:       'block',
      videoSplitHTML:            'true',
      linkAlwaysBlank:           true,
      toolbarInline:             true,
      charCounterCount:          false,
      toolbarButtons:            buttons,
      toolbarButtonsMD:          buttons,
      toolbarButtonsSM:          buttons,
      toolbarButtonsXS:          buttons,
      shortcutsEnabled:          ['bold', 'italic', 'underline'],
      quickInsertButtons:        ['image', 'file', 'video'],
      enter:                     $.FroalaEditor.ENTER_BR,
      placeholderText:           false,
      immediateReactModelUpdate: true,
      key:                       'MC1D2D1G2lG4J4A14A7D3D6F6C2C3F3gSXSE1LHAFJVCXCLS==',
      events:                    {
        'froalaEditor.focus':       Container.onFocus,
        'froalaEditor.blur':        () => { this.editor.selection.save(); Container.onBlur(); },
        'froalaEditor.initialized': this.bindFroalaEvents
      }
    };

    return (
      <Detached
        zIndex={99999}
        isOpen={isOpen}
        positionTarget={document.getElementById(`chat-${current.get('id')}`) || document.getElementById('im-overflow')}
        positionMy="left-43 top-2"
      >
        <div className="ui popup left bottom im chat drawer">
          <div className="im header">{header}</div>
          {this.searchHeader()}
          {this.groupHeader()}
          <MessageList
            loadingMessages={loadingMessages}
            current={current}
            messages={messages}
            me={me}
            agents={agents}
            people={people}
            teams={teams}
            departments={departments}
            searchQuery={searchQuery}
            markNewMessages={markNewMessages}
            onScroll={onScroll}
            onAgentClick={onAgentClick}
            onSearchMessageClick={this.onSearchMessageClick}
          />
          {enabled ? (<div className="reply">
            <form onSubmit={this.handleSubmit} id="replyForm">
              <FroalaEditor
                tag="textarea"
                config={froalaConfig}
                model={this.state.message}
                onModelChange={this.handleChange}
                onManualControllerReady={this.initFroala}
              />
              <i
                className={classNames('fa fa-link reply-icon', { inactive: Object.keys(this.props.activeTabs).length < 1 })}
                ref={(c) => { this.linkTrigger = c; }}
                onClick={this.openLink}
              />
              <i
                className="fa fa-paperclip reply-icon"
                ref={(c) => { this.attachTrigger = c; }}
                onClick={this.openAttach}
              />
              <i
                className="far fa-smile reply-icon emoji trigger"
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
            {Object.keys(this.props.activeTabs).length ? this.renderAttachList() : null}
          </div>) : null }
        </div>
      </Detached>
    );
  }
}

function CloseChat(props) {
  return (
    <span className="button close-im" onClick={props.onClick}>
      <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/im/close-open-im.svg`} />
    </span>
  );
}

CloseChat.propTypes = {
  onClick: PropTypes.func.isRequired
};

export default Container;
