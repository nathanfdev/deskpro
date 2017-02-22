import 'froala-editor/js/froala_editor.pkgd.min';
import React, { PropTypes } from 'react';
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
emojione.imageType = 'png';
emojione.sprites = true;

class Container extends React.Component {
  static propTypes = {
    me:              PropTypes.object.isRequired,
    agents:          PropTypes.object.isRequired,
    people:          PropTypes.object.isRequired,
    departments:     PropTypes.object.isRequired,
    teams:           PropTypes.object.isRequired,
    current:         PropTypes.object.isRequired,
    searchQuery:     PropTypes.string,
    isOpen:          PropTypes.bool.isRequired,
    onClose:         PropTypes.func,
    saveDraft:       PropTypes.func,
    onSubmit:        PropTypes.func,
    onChange:        PropTypes.func,
    activeTabs:      PropTypes.object,
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
      linkDrawerOpened:  false
    };

    this.destroyEditor    = () => {};
    this.openEmoji        = this.openEmoji.bind(this);
    this.closeEmoji       = this.closeEmoji.bind(this);
    this.handleChange     = this.handleChange.bind(this);
    this.addEmoji         = this.addEmoji.bind(this);
    this.handleSubmit     = this.handleSubmit.bind(this);
    this.handleKeydown    = this.handleKeydown.bind(this);
    this.initFroala       = this.initFroala.bind(this);
    this.bindFroalaEvents = this.bindFroalaEvents.bind(this);
    this.openLink         = this.openLink.bind(this);
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
    this.destroyEditor();
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
    const { agents, people, teams, departments, current, me, openGroupDrawer } = this.props;
    const props = { agents, people, teams, departments, current, me, openGroupDrawer };

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
          className={classNames('remove icon')}
          onClick={() => { this.closeContainer(); }}
        />
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
    const newState = { mounted: true, searching: false, expandedHeader: false };
    if (props.drafts && props.drafts[props.current.get('id')]) {
      newState.message = props.drafts[props.current.get('id')];
    } else {
      newState.message = '';
    }

    this.setState(newState);
    this.props.onChatSearch('');
  }

  openEmoji() {
    this.setState({ emojiOpened: true });
  }

  closeEmoji() {
    this.setState({ emojiOpened: false });
  }

  closeContainer() {
    this.closeEmoji();
    this.setState({ searching: false, expandedHeader: false });
    this.props.onChatSearch('');
    this.props.onClose(this.props.current.get('id'));
  }

  addEmoji(emoji) {
    const editor = this.editor;
    const html = emoji.shortname;
    editor.events.focus();
    editor.html.insert(html);
  }

  handleChange(text) {
    this.setState({ message: text });
    this.props.onChange(text);
  }

  handleSubmit(event) {
    event.preventDefault();
    this.props.onSubmit(this.state.message);
    this.props.saveDraft(this.props.current.get('id'), '');
    this.setState({ message: '' });
  }

  initFroala(initControls) {
    initControls.initialize();
    this.destroyEditor = initControls.destroy;
  }

  bindFroalaEvents(e, editor) {
    this.editor = editor;
    editor.events.on('keydown', this.handleKeydown, true);
    editor.events.focus();
  }

  handleKeydown(e) {
    if (e.keyCode === 13 && (e.ctrlKey || e.metaKey)) {
      e.stopPropagation();
      this.handleSubmit(e);
      return false;
    }
    return e;
  }

  openLink() {
    this.setState({ linkDrawerOpened: true });
  }

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
                  {AvatarHelper.renderAgentAvatar(agent, 24, className)}
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
    const propertyName = `${item.tabType}_id`;
    const message = `{{${item.tabType.charAt(0).toLowerCase()}-${item.page.meta[propertyName]}}}: ${item.title}`;
    this.editor.events.focus();
    this.editor.html.insert(message);
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
        <ClickOut onClickOut={() => { this.setState({ linkDrawerOpened: false }); }}>
          <div className="attach-list">
            <Header content="Current tabs" level={4} className="attach-header" />
            <List elements={items} />
          </div>
        </ClickOut>
      </Detached>)
      : null;
  }

  render() {
    const { isOpen, loadingMessages, onScroll, markNewMessages, searchQuery, onAgentClick } = this.props;
    const { current, messages, me, agents, teams, departments, people } = this.props;
    const header = this.getHeader();
    let enabled = true;
    if (current.get('chat_type') === 'agent') {
      const agentId = this.headerHelper.getAgentId(current);
      if (agentId && !agents.get(agentId)) {
        enabled = false;
      }
    }

    const froalaConfig = {
      imageUploadMethod:         'POST',
      imageUploadParams:         { _rt: window.DP_REQUEST_TOKEN, json: true },
      imageUploadURL:            `${BASE_URL}agent/misc/accept-redactor-image-upload`, // eslint-disable-line no-undef
      fileUploadMethod:          'POST',
      fileUploadParams:          { _rt: window.DP_REQUEST_TOKEN, json: true },
      fileUploadURL:             `${BASE_URL}agent/misc/accept-redactor-file-upload`, // eslint-disable-line no-undef
      toolbarInline:             true,
      charCounterCount:          false,
      toolbarButtons:            ['bold', 'italic', 'underline', 'strikeThrough', 'color', '-', 'align', 'formatOL', 'formatUL', 'insertImage', '-', 'insertLink', 'insertFile', 'insertVideo', 'undo', 'redo'],
      shortcutsEnabled:          ['bold', 'italic', 'underline'],
      enter:                     $.FroalaEditor.ENTER_BR,
      placeholderText:           false,
      immediateReactModelUpdate: true,
      key:                       'qENARBFSTb1G1QJg1RA==',
      events:                    {
        'froalaEditor.focus':       () => { this.editor.selection.restore(); Container.onFocus(); },
        'froalaEditor.blur':        () => { this.editor.selection.save(); Container.onBlur(); },
        'froalaEditor.initialized': this.bindFroalaEvents
      }
    };

    return (
      <Detached
        zIndex={99999}
        isOpen={isOpen}
        positionTarget={document.getElementById(`chat-${current.get('id')}`)}
        positionMy="left-43 top-2"
      >
        <div className="ui popup left bottom im chat drawer">
          <div className="im header">{header}</div>
          {this.searchHeader()}
          {this.groupHeader()}
          <div className="box">
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
            />
          </div>
          {enabled ? (<div className="reply">
            <form onSubmit={this.handleSubmit}>
              <FroalaEditor
                tag="textarea"
                className="textarea"
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
            {Object.keys(this.props.activeTabs).length ? this.renderAttachList() : null}
          </div>) : null }
        </div>
      </Detached>
    );
  }
}

export default Container;
