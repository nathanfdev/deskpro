import React, { PropTypes } from 'react';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/PersonAvatar';
import { chooseColor } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/colors';

export class EveryoneSegment extends React.Component {

  static propTypes = {
    agents:             PropTypes.object.isRequired,
    onParticipantClick: PropTypes.func.isRequired
  };

  static getAvatar(agent) {
    const classes = ['ui', 'avatar', 'image', 'im'];
    if (!agent.get('online')) {
      classes.push('offline');
    }

    return <PersonAvatar person={agent} size={24} classes={classes} color={chooseColor(agent.get('id'))} />;
  }

  render() {
    return (
      <Segment
        classes={['everyone-list']}
        raised
        vertical
      >
        <div onClick={() => this.props.onParticipantClick(null, 'everyone')}>
          {this.props.agents.map(agent => EveryoneSegment.getAvatar(agent))}
        </div>
      </Segment>);
  }
}
