import React, { PropTypes } from 'react';
import moment from 'moment';
import classNames from 'classnames';
import emojione from 'emojione';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { darkerColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import AvatarHelper from '../IMTabs/AvatarHelper';

class Message extends React.Component
{
  static propTypes = {
    me:           PropTypes.object.isRequired,
    message:      PropTypes.object.isRequired,
    previous:     PropTypes.oneOfType([PropTypes.object, PropTypes.bool]).isRequired,
    searchQuery:  PropTypes.string,
    agent:        PropTypes.object,
    onAgentClick: PropTypes.func.isRequired
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

  getMessage() {
    let message = this.props.message.message;
    emojione.imagePathSVGSprites = `./..${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/emoticons/emojione.sprites.svg`;
    emojione.imageType = 'svg';
    emojione.sprites = true;

    if (this.props.searchQuery) {
      message = message.replace(this.props.searchQuery, `<span class="search result">${this.props.searchQuery}</span>`);
    }
    message = emojione.shortnameToImage(message);
    return {
      __html: message
    };
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

  render() {
    const { searchQuery, message, me, agent, onAgentClick } = this.props;
    const my = message.person === me.get('id') && !searchQuery;
    return (<Segment className={classNames('row', { search: searchQuery, result: searchQuery, my })}>
      {this.dateSep()}
      {my || searchQuery ? this.timestamp() : null}
      <div className="message">
        {searchQuery ? <div className="avatar wrapper">{AvatarHelper.renderAgentAvatar(agent, 20)}</div> : null}
        {!my && !searchQuery
          ? (
            <div
              style={{ color: darkerColor(agent.get('id'), 0.2) }}
              onClick={() => onAgentClick(agent.get('id'), 'agent')}
              className="agent name"
            >
              {message.person_name}
            </div>
            )
          : null}
        <div className="content" dangerouslySetInnerHTML={this.getMessage()} />
      </div>
      {!my && !searchQuery ? this.timestamp() : null}
    </Segment>);
  }
}

export default Message;
