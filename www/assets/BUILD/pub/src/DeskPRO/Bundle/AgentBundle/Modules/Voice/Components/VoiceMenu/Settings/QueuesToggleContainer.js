import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { voiceAgentsSelector, voiceOnlineAgentsSelector } from '../../../Selectors/agents';
import { loadQueues, updateQueue } from '../../../Actions/queueActions';
import { allQueuesSelector } from '../../../Selectors/queue';
import Queues from './Queues';

@connect(state => ({
  me:           meSelector(state),
  queues:       allQueuesSelector(state),
  agents:       voiceAgentsSelector(state),
  onlineAgents: voiceOnlineAgentsSelector(state)
}))
class QueuesToggleContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      saving: false
    };
  }

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(loadQueues());
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

export default QueuesToggleContainer;
