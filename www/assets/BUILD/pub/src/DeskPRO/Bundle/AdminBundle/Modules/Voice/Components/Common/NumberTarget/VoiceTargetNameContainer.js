import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { loadAgents } from '../../../../Application/Actions/peopleActions';
import { loadQueues } from '../../../Actions/queueActions';
import { loadAutoAttendants } from '../../../Actions/autoAttendantActions';
import { allAgentsSelector } from '../../../../Application/Selectors/people';
import { allQueuesSelector } from '../../../Selectors/queue';
import { allAutoAttendantsSelector } from '../../../Selectors/autoAttendant';

@connect(state => ({
  agents:         allAgentsSelector(state),
  queues:         allQueuesSelector(state),
  autoAttendants: allAutoAttendantsSelector(state)
}))
class VoiceTargetNameContainer extends React.Component {

  static propTypes = {
    target:         PropTypes.object,
    agents:         PropTypes.object,
    queues:         PropTypes.object,
    autoAttendants: PropTypes.object,
    dispatch:       PropTypes.func,
    children:       PropTypes.node
  };

  componentDidMount() {
    const { dispatch, target } = this.props;

    switch (target.get('type')) {
      case 'agent':
        dispatch(loadAgents());
        break;
      case 'queue':
        dispatch(loadQueues());
        break;
      case 'auto_attendant':
        dispatch(loadAutoAttendants());
        break;
      default:
        break;
    }
  }

  render() {
    const { target } = this.props;
    const { agents, queues, autoAttendants } = this.props;
    const targetType = target.get('type');

    let targetName;
    switch (target.get('type')) {
      case 'agent': {
        const agent = agents && agents.get(target.get('agent'));
        targetName = agent ? agent.get('name') : '';
        break;
      }
      case 'queue': {
        const queue = queues && queues.get(target.get('queue'));
        targetName = queue ? queue.get('name') : '';
        break;
      }
      case 'auto_attendant': {
        const autoAttendant = autoAttendants && autoAttendants.get(target.get('auto_attendant'));
        targetName = autoAttendant ? autoAttendant.get('name') : '';
        break;
      }
      default:
        targetName = '';
        break;
    }

    let { children } = this.props;
    if (!children) {
      children = <VoiceTargetName />;
    }

    return React.cloneElement(children, { targetName, targetType });
  }
}

class VoiceTargetName extends React.Component {

  static propTypes = {
    targetName: PropTypes.string
  };

  render() {
    const { targetName } = this.props;

    return (
      <span>{targetName}</span>
    );
  }
}

export default VoiceTargetNameContainer;
