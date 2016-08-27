import React, { PropTypes } from 'react';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import classNames from 'classnames';
import TimeAgo from 'react-timeago';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';

class AgentList extends React.Component {

  static propTypes = {
    agents:        PropTypes.object.isRequired,
    notifications: PropTypes.object.isRequired
  };

  static getAvatar(agent) {
    return (<PersonAvatar
      color={chooseColor(agent.get('id'))}
      person={agent} size={24}
      classes={['ui avatar image im']}
    />);
  }

  getItem(agent) {
    const notificationCount = this.props.notifications.get(`${agent.get('id')}`);
    const classes = ['im', 'agent'];
    if (!agent.get('online')) {
      classes.push('offline');
    }

    return (
      <ListElement key={agent.get('id')} classes={classes} imageNode={AgentList.getAvatar(agent)}>
        <div className="content agent">
          <span className="name">{agent.get('name')}</span>
          <span
            className={classNames(
                ['ui', 'knuckles', 'label', 'message-counter'],
                { grey: notificationCount < 1 })
            }
          >
            {notificationCount}
          </span>
        </div>
        <div className="timestamp last-seen content right floated">
          {agent.get('last_seen') ? <TimeAgo date={agent.get('last_seen')} /> : 'never'}
        </div>

      </ListElement>
    );
  }

  getItems() {
    return this.props.agents.map(agent => this.getItem(agent));
  }

  render() {
    return (
      <List classes={['im', 'middle', 'aligned', 'selection']}>
        {this.getItems()}
      </List>);
  }
}

export default AgentList;
