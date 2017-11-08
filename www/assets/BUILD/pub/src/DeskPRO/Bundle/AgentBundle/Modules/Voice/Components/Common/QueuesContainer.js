import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { voiceAgentsSelector } from '../../Selectors/agents';
import { loadQueues } from '../../Actions/queueActions';
import { allQueuesSelector } from '../../Selectors/queue';

@connect(state => ({
  me:     meSelector(state),
  queues: allQueuesSelector(state),
  agents: voiceAgentsSelector(state)
}))
class QueuesContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.node
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(loadQueues());
  }

  render() {
    const { children } = this.props;

    return React.cloneElement(children, { ...children.props, ...this.props });
  }
}

export default QueuesContainer;
