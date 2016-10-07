import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import AvatarHelper from './AvatarHelper';
import AbstractList from './AbstractList';

class AgentList extends AbstractList {

  static propTypes = {
    notifications: PropTypes.object.isRequired
  };

  getAvatar = AvatarHelper.renderAgentAvatar;

  getItem(agent) {
    const notificationCount = this.props.notifications.get(`${agent.get('id')}`) || 0;
    const className = ['im', 'agent'];
    if (!agent.get('online')) {
      className.push('offline');
    }

    return (
      <ListElement
        key={agent.get('id')}
        className={className}
        imageNode={this.getAvatar(agent)}
      >
        <div
          onClick={() => this.props.onParticipantClick(agent.get('id'), 'agent')}
          className="content agent"
        >
          <div className="header">{agent.get('name')}</div>
          <div
            className={classNames(
                ['ui', 'knuckles', 'label', 'message-counter'],
                { grey: notificationCount < 1 })
            }
          >
            {notificationCount}
          </div>
        </div>
        <div className="timestamp last-seen content right floated">
          {AbstractList.getTimestamp(agent.get('last_seen'))}
        </div>

      </ListElement>
    );
  }

  getItems() {
    return this.props.agents.map(agent => this.getItem(agent));
  }

  render() {
    return (
      <List className="im middle aligned selection">
        {this.getItems()}
      </List>);
  }
}

export default AgentList;
