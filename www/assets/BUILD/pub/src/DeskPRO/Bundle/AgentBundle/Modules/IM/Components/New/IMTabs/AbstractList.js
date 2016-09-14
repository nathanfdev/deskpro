import React, { PropTypes } from 'react';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import TimeAgo from 'react-timeago';
import moment from 'moment';

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
    const classes = ['im', type];
    let size = item.get('agents').size;
    if (size > 2) {
      size -= 1;
    } else {
      size = 0;
    }
    return (
      <ListElement
        key={item.get('id')}
        classes={classes}
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

  getAgents(team) {
    return team.get('agents').map(
      (agentId) => {
        if (agentId === this.props.me.get('id')) {
          return null;
        }

        const classes = ['ui avatar image im'];
        const agent = this.props.agents.get(`${agentId}`);
        if (!agent.get('online')) {
          classes.push('offline');
        }

        return (<PersonAvatar
          person={agent}
          size={14}
          classes={classes}
          color={chooseColor(agent)}
        />);
      }
    );
  }

  render() {
    return (
      <List classes={['im', 'middle', 'aligned', 'selection']}>
        {this.getItems()}
      </List>);
  }
}

export default AbstractList;
