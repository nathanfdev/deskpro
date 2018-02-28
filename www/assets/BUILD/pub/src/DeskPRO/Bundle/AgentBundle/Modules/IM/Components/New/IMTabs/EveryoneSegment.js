import PropTypes from 'prop-types';
import React from 'react';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import AvatarHelper from './AvatarHelper';

class EveryoneSegment extends React.Component {

  static propTypes = {
    agents:             PropTypes.object.isRequired,
    onParticipantClick: PropTypes.func.isRequired
  };

  render() {
    return (
      <Segment
        className="everyone-list"
        raised
        vertical
      >
        <div onClick={() => this.props.onParticipantClick(0, 'everyone')}>
          {this.props.agents.slice(0, 14).toArray().map(agent => AvatarHelper.renderAgentAvatar(agent, 24, [], agent.get('name')))}
        </div>
      </Segment>);
  }
}

export default EveryoneSegment;
