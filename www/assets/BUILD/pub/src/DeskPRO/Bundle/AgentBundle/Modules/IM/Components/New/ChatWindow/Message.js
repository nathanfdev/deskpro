import React, { PropTypes } from 'react';
import moment from 'moment';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import classNames from 'classnames';
import emojione from 'emojione';
import AvatarHelper from '../IMTabs/AvatarHelper';

emojione.imagePathSVGSprites = './../assets/BUILD/pub/build/DeskPRO/Bundle/AgentBundle/Resources/img/emoticons/emojione.sprites.svg';
emojione.imageType = 'svg';
emojione.sprites = true;

class Message extends React.Component
{
  static propTypes = {
    me:          PropTypes.object.isRequired,
    message:     PropTypes.object.isRequired,
    previous:    PropTypes.oneOfType([PropTypes.object, PropTypes.bool]).isRequired,
    searchQuery: PropTypes.string,
    agent:       PropTypes.object
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

    if (this.props.searchQuery) {
      message = message.replace(this.props.searchQuery, `<span class="search result">${this.props.searchQuery}</span>`);
    }
    message = emojione.shortnameToImage(message);
    return {
      __html: message
    };
  }

  dateSep() {
    const date = moment(this.props.message.date_created);

    const previousDate = moment(this.props.previous.date_created);
    if ((this.props.previous && previousDate.dayOfYear() !== date.dayOfYear()) || !this.props.previous) {
      return Message.renderSeparator(this.props.message.date_created);
    }

    return null;
  }

  timestamp() {
    const m = moment(this.props.message.date_created);
    const classes = ['timestamp'];
    if (this.props.searchQuery) {
      classes.push('ui fitted horizontal divider');
    }

    return (<div className={classNames(classes)}>
      {m.format('h:mm a')}
    </div>);
  }

  render() {
    const { searchQuery, message, me, agent } = this.props;
    const my = message.person === me.get('id') && !searchQuery;
    return (<Segment className={classNames('row', { search: searchQuery, result: searchQuery, my })}>
      {this.dateSep()}
      {my || searchQuery ? this.timestamp() : null}
      <div className="message">
        {searchQuery ? <div className="avatar wrapper">{AvatarHelper.renderAgentAvatar(agent, 14)}</div> : null}
        {!my && !searchQuery ? <div className="agent name">{message.person_name}</div> : null}
        <div className="content" dangerouslySetInnerHTML={this.getMessage()} />
      </div>
      {!my && !searchQuery ? this.timestamp() : null}
    </Segment>);
  }
}

export default Message;
