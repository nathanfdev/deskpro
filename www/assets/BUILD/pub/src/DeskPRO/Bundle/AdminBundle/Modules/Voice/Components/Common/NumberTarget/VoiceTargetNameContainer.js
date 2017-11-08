import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { loadAgents } from '../../../../Application/Actions/peopleActions';
import { loadQueues } from '../../../Actions/queueActions';
import { loadAutoAttendants } from '../../../Actions/autoAttendantActions';
import { allAgentsSelector } from '../../../../Application/Selectors/people';
import { allQueuesSelector } from '../../../Selectors/queue';
import { allAutoAttendantsSelector } from '../../../Selectors/autoAttendant';
import { replaceRoute } from '../../../../../Services/history';

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

    if (!target) {
      return;
    }

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

  onRedirectToTarget = (event) => {
    event.preventDefault();
    const targetUrl = this.getTargetUrl();
    if (!targetUrl) {
      return;
    }

    replaceRoute(targetUrl);
  };

  getTargetObject() {
    const { target } = this.props;
    const { agents, queues, autoAttendants } = this.props;

    let targetObject;

    if (target) {
      const targetType = target.get('type');
      const targetId   = target.get('target');

      switch (targetType) {
        case 'agent': {
          targetObject = agents && agents.get(targetId);
          break;
        }
        case 'queue': {
          targetObject = queues && queues.get(targetId);
          break;
        }
        case 'auto_attendant': {
          targetObject = autoAttendants && autoAttendants.get(targetId);
          break;
        }
        default:
          break;
      }
    }

    if (!targetObject) {
      targetObject = Immutable.fromJS({});
    }

    return targetObject;
  }

  getTargetUrl() {
    const { target } = this.props;
    const targetObject = this.getTargetObject();
    const targetId = targetObject.get('id');

    if (!targetId) {
      return '';
    }

    switch (target.get('type')) {
      case 'agent':
        return `/agents/agents/${targetId}`;
      case 'queue':
        return `/voice_channel/queues/${targetId}`;
      case 'auto_attendant':
        return `/voice_channel/auto_attendants/${targetId}`;
      default:
        return '';
    }
  }

  render() {
    const { target } = this.props;
    if (!target) {
      return null;
    }

    const targetType = target.get('type');
    const targetName = this.getTargetObject().get('name');

    let { children } = this.props;
    if (!children) {
      children = <VoiceTargetName />;
    }

    return React.cloneElement(children, {
      targetName,
      targetType,
      onRedirectToTarget: this.onRedirectToTarget
    });
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
