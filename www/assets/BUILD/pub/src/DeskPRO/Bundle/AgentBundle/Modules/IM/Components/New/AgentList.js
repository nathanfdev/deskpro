import React, { PropTypes } from 'react';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import classNames from 'classnames';
import TimeAgo from 'react-timeago';
import moment from 'moment';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';

class AgentList extends React.Component {

  static propTypes = {
    agents:             PropTypes.object.isRequired,
    notifications:      PropTypes.object.isRequired,
    onParticipantClick: PropTypes.func.isRequired
  };

  static getAvatar(agent) {
    return (<PersonAvatar
      color={chooseColor(agent.get('id'))}
      person={agent} size={24}
      classes={['ui avatar image im']}
    />);
  }

  static getTimestamp(date) {
    const m = date ? moment(date) : null;
    if (!m) return 'never';

    const now = moment();
    const seen = now.unix() - m.unix();
    return m && (seen > 3600 && seen < 60 * 60 * 24) ? m.format('h:mm a') : <TimeAgo date={date} />;
  }

  getItem(agent) {
    const notificationCount = this.props.notifications.get(`${agent.get('id')}`);
    const classes = ['im', 'agent'];
    if (!agent.get('online')) {
      classes.push('offline');
    }

    return (
      <ListElement

        key={agent.get('id')}
        classes={classes}
        imageNode={AgentList.getAvatar(agent)}
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
          {AgentList.getTimestamp(agent.get('last_seen'))}
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
