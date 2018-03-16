import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';
import $ from 'jquery';
import 'mark.js/dist/jquery.mark';
import classNames from 'classnames';
import emojione from 'emojione';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { darkerColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import AvatarHelper from '../IMTabs/AvatarHelper';

class Message extends React.Component {
  static propTypes = {
    me:                   PropTypes.object.isRequired,
    message:              PropTypes.object.isRequired,
    previous:             PropTypes.oneOfType([PropTypes.object, PropTypes.bool]).isRequired,
    searchQuery:          PropTypes.string,
    person:               PropTypes.object,
    isAgent:              PropTypes.bool,
    onAgentClick:         PropTypes.func.isRequired,
    onSearchMessageClick: PropTypes.func.isRequired,
    scrollStub:           PropTypes.func.isRequired
  };

  static renderSeparator(dateCreated) {
    const date = moment(dateCreated);
    const now = moment();
    let today = false;
    if (now.dayOfYear() === date.dayOfYear() && now.year() === date.year()) {
      today = true;
    }
    return (
      <div className="ui date separator">
        <span>{!today ? date.format('Do MMM YYYY') : 'Today'}</span>
      </div>
    );
  }

  // it's just a copy paste version of AgentChatWin::formatMessage
  static formatMessage(message) {
    const idMap = {
      t: { title: 'Ticket', url: 'agent/tickets/' },
      p: { title: 'Person', url: 'agent/people/' },
      o: { title: 'Organization', url: 'agent/organizations/' },
      a: { title: 'Article', url: 'agent/kb/article/' },
      n: { title: 'News', url: 'agent/news/post/' },
      d: { title: 'Download', url: 'agent/downloads/file/' },
      f: { title: 'Feedback', url: 'agent/feedback/view/' },
      u: { title: 'Userchat', url: 'agent/chat/view/' }
    };
    let newMessage = message;

    Object.each(idMap, (info, prefix) => {
      const re = new RegExp(`\\{\\{\\s*${prefix}\\-([0-9]+)\\s*\\}\\}`, 'g');
      newMessage = newMessage.replace(re, `<a data-route="page:${window.BASE_URL}${info.url}$1">${info.title} #$1</a>`);
      return null;
    });

    return newMessage;
  }

  constructor(props) {
    super(props);
    this.messageNode = null;
  }

  componentDidMount() {
    $('a:not([data-route])', this.messageNode).attr('target', '_blank');
    const parent = $('img', this.messageNode).closest('.box');
    $('.message img:last-child', parent).last().on('load', this.props.scrollStub);
    $('.message img:last-child', parent).last().on('load', this.props.scrollStub);
  }

  componentDidUpdate() {
    if (this.props.searchQuery) {
      const $context = $('.content', this.messageNode);
      $context.unmark();
      $context.mark(this.props.searchQuery, { element: 'span', className: 'search-result' });
    }
  }

  getMessage() {
    let message = this.props.message.message;
    emojione.imagePathSVGSprites = `./..${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/emoticons/emojione.sprites.svg`;
    emojione.imageType = 'svg';
    emojione.sprites = true;

    message = emojione.shortnameToImage(message);
    message = Message.formatMessage(message);
    return {
      __html: message
    };
  }

  getMessageObject() {
    return this.props.message;
  }

  getNode() {
    return this.messageNode;
  }

  timestamp() {
    const { message, searchQuery } = this.props;

    const m = moment(message.date_created);
    const classes = ['timestamp'];
    if (searchQuery) {
      classes.push('ui fitted horizontal divider');
    }

    return (<div className={classNames(classes)}>
      {m.format('h:mm a')}
    </div>);
  }

  dateSep() {
    const { message, previous } = this.props;
    const date = moment(message.date_created);
    const previousDate = moment(previous.date_created);

    if ((previous && previousDate.dayOfYear() !== date.dayOfYear()) || !previous) {
      return Message.renderSeparator(message.date_created);
    }

    return null;
  }

  isSearchResult() {
    return this.props.searchQuery;
  }

  render() {
    const { searchQuery, message, me, person, isAgent, onAgentClick, onSearchMessageClick } = this.props;
    const my = message.person === me.get('id') && !searchQuery;
    const contentProps = { className: 'content dont-break-out', dangerouslySetInnerHTML: this.getMessage() };
    const messageProps = { className: 'message', ref: (c) => { this.messageNode = c; } };
    if (!searchQuery) {
      messageProps.id = `chat-${message.chat}-message-${message.id}`;
      messageProps.ref = (x) => { this.messageNode = x; };
    }

    if (searchQuery) {
      contentProps.onClick = (e) => { if (e.target.tagName !== 'a') { onSearchMessageClick(this); } };
    }
    return (<Segment className={classNames('row', { search: searchQuery, result: searchQuery, my })}>
      {this.dateSep()}
      {my || searchQuery ? this.timestamp() : null}
      <div {...messageProps} >
        {
          searchQuery
          ? (<div className="avatar wrapper">
            {
              person
              ? AvatarHelper.renderAgentAvatar(person, 20)
              : AvatarHelper.renderAvatar(message.person_name.substr(0, 2).toUpperCase(), message.person, 20)
            }
          </div>)
          : null
        }
        {!my && !searchQuery
          ? (
            <div
              style={{ color: darkerColor(message.person, 0.2) }}
              onClick={() => {
                if (person && isAgent) {
                  onAgentClick(person.get('id'), 'agent');
                }
              }}
              className={classNames('agent name', { disabled: !isAgent })}
            >
              {message.person_name}
            </div>
            )
          : null}
        <div {...contentProps} />
      </div>
      {!my && !searchQuery ? this.timestamp() : null}
    </Segment>);
  }
}

export default Message;
