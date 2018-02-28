import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import TimeAgo from '@deskpro/react-timeago';
import moment from 'moment';
import AvatarHelper from './AvatarHelper';

class AbstractList extends React.Component {
  static propTypes = {
    me:                 PropTypes.object.isRequired,
    agents:             PropTypes.object.isRequired,
    onParticipantClick: PropTypes.func.isRequired
  };

  static getTimestamp(date) {
    const m = date ? moment(date) : null;
    if (!m) return 'never';

    const now = moment();
    const diff = now.unix() - m.unix();
    const mObj = m.toObject();
    const nObj = now.toObject();
    if (nObj.years === mObj.years && nObj.months === mObj.months && (nObj.date - mObj.date) === 1) {
      return 'Yesterday';
    }
    return m && (diff < 60 * 60 * 24) ? m.format('h:mm a') : <TimeAgo date={date} />;
  }

  getItem(item, type, titleProp) {
    let size = this.getAgents(item).size;
    if (size > 2) {
      size -= 1;
    } else {
      size = 0;
    }
    return (
      <ListElement
        key={`${type}_${item.get('id')}`}
        className={`im ${type}`}
        imageNode={this.getAvatar(item)}
      >
        <div
          onClick={() => this.props.onParticipantClick(item.get('id'), type)}
          className={`content ${type}`}
        >
          <div className="header">
            {item.get(titleProp)}
            {size ? <span className="agents-counter">({size})</span> : null}
          </div>
          <span className="agents-list">{this.getAgents(item)}</span>
        </div>
      </ListElement>
    );
  }

  getAgents(container) {
    return container.get('agents').map(
      (agentId) => {
        if (agentId === this.props.me.get('id')) {
          return null;
        }

        const className = ['ui avatar image im'];
        const agent = this.props.agents.get(agentId);
        if (!agent) {
          return null;
        }
        if (!agent.get('online')) {
          className.push('offline');
        }

        return AvatarHelper.renderAgentAvatar(agent, 20, classNames(className), agent.get('name'));
      }
    );
  }

  render() {
    return (
      <List className="im middle aligned selection">
        {this.getItems()}
      </List>);
  }
}

export default AbstractList;
