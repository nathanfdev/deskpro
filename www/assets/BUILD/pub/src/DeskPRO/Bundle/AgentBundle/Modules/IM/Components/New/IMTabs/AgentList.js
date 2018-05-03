import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import AvatarHelper from './AvatarHelper';
import AbstractList from './AbstractList';

class AgentList extends AbstractList {

  static propTypes = {
    counts: PropTypes.object.isRequired
  };

  getAvatar = AvatarHelper.renderAgentAvatar;

  getItem(agent) {
    if (agent.get('id') === this.props.me.get('id')) {
      return null;
    }

    const className = ['im', 'agent', agent.get('online') ? 'online' : 'offline'];

    return (
      <ListElement
        key={agent.get('id')}
        className={classNames(className)}
        imageNode={this.getAvatar(agent, 24, [], agent.get('name'))}
      >
        <div
          onClick={() => this.props.onParticipantClick(agent.get('id'), 'agent')}
          className="content agent"
        >
          <div className="header">{agent.get('name')}</div>
        </div>
      </ListElement>
    );
  }

  getItems() {
    return this.props.agents.toArray().sort((a, b) => {
      if (a.get('name') < b.get('name')) {
        return -1;
      } else if (a.get('name') > b.get('name')) {
        return 1;
      }

      return 0;
    }).map(agent => this.getItem(agent));
  }

  render() {
    return (
      <List className="im middle aligned selection">
        {this.getItems()}
      </List>);
  }
}

export default AgentList;
