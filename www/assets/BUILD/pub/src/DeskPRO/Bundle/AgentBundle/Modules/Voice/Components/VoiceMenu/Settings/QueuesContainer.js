import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { voiceAgentsSelector } from '../../../Selectors/agents';
import { loadQueues, updateQueue, loadAverageWaitingTime } from '../../../Actions/queueActions';
import { allQueuesSelector } from '../../../Selectors/queue';
import { onlineAgentsSelector, forwardingAgentsSelector } from '../../../Selectors/client';
import Queues from './Queues';

@connect(state => ({
  me:                 meSelector(state),
  queues:             allQueuesSelector(state),
  agents:             voiceAgentsSelector(state),
  onlineAgentIds:     onlineAgentsSelector(state),
  forwardingAgentIds: forwardingAgentsSelector(state)
}))
class QueuesContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      saving:       false,
      waitingUsers: {}
    };
  }

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(loadQueues());

    const promise = dispatch(loadAverageWaitingTime());
    promise.success(({ data }) => {
      this.setState({
        waitingUsers: data || {}
      });
    });

    const messageBroker = window.DeskPRO_Window.getMessageBroker();
    messageBroker.addMessageListener('agent.voice.queue.average-waiting-time', (data) => {
      const { waitingUsers } = this.state;
      waitingUsers[data.queue] = data;

      this.setState({ waitingUsers });
    });
  }

  onChange = (queue, enabled) => {
    if (this.state.saving) {
      return;
    }

    this.setState({
      saving: true
    });

    const { dispatch } = this.props;
    const promise = dispatch(updateQueue(queue.get('id'), { enabled }));
    promise.success(() => {
      this.setState({
        saving: false
      });
    });
    promise.error(() => {
      this.setState({
        saving: false
      });
    });
  };

  render() {
    return (
      <Queues
        {...this.props}
        {...this.state}
        onChange={this.onChange}
      />
    );
  }
}

export default QueuesContainer;
