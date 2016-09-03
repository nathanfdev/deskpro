import React, { PropTypes } from 'react';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';

export class AbstractList extends React.Component {
  static propTypes = {
    me:                 PropTypes.object.isRequired,
    agents:             PropTypes.object.isRequired,
    onParticipantClick: PropTypes.func.isRequired
  };

  getItem(item, type, titleProp) {
    const classes = ['im', type];

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
            <span className="agents-counter">({item.get('agents').length - 1})</span>
            <span className="agents-list">{this.getAgents(item)}</span>
          </div>
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
          size={12}
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
