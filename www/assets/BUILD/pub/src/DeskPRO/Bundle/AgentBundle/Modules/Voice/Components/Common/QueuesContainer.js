import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { agentsSelector, meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { loadQueues } from '../../Actions/queueActions';
import { allQueuesSelector } from '../../Selectors/queue';

@connect(state => ({
  me:     meSelector(state),
  queues: allQueuesSelector(state),
  agents: agentsSelector(state)
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
