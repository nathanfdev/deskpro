import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import QueueForm from './QueueForm';
import { allAgentsSelector, allAgentTeamsSelector, isAgentsLoadedSelector, isAgentTeamsLoadedSelector } from '../../../../Application/Selectors/people';
import { replaceRoute } from '../../../../../Services/history';
import { loadAgents, loadAgentTeams } from '../../../../Application/Actions/peopleActions';
import { updateQueue, loadQueues, deleteQueue } from '../../../Actions/queueActions';
import { allQueuesSelector, isQueuesLoadedSelector } from '../../../Selectors/queue';
import LoadingPage from '../../../../Common/Components/LoadingPage';

@connect(state => ({
  agents:           allAgentsSelector(state),
  agentsLoaded:     isAgentsLoadedSelector(state),
  agentTeams:       allAgentTeamsSelector(state),
  agentTeamsLoaded: isAgentTeamsLoadedSelector(state),
  queues:           allQueuesSelector(state),
  queuesLoaded:     isQueuesLoadedSelector(state)
}))
class EditQueueContainer extends React.Component {

  static propTypes = {
    agentsLoaded:     PropTypes.bool,
    agentTeamsLoaded: PropTypes.bool,
    queuesLoaded:     PropTypes.bool,
    queues:           PropTypes.object,
    params:           PropTypes.object,
    dispatch:         PropTypes.func
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAgents());
    dispatch(loadAgentTeams());
    dispatch(loadQueues());
  }

  getQueueId = () => parseInt(this.props.params.queueId, 10);
  returnBack = () => {
    replaceRoute('/chat/queues');
  };

  deleteQueue = () => {
    const { dispatch } = this.props;
    const promise = dispatch(deleteQueue(this.getQueueId()));

    promise.success(() => {
      replaceRoute('/chat/queues');
    });

    return promise;
  };

  submitData = (data) => {
    const promise = this.props.dispatch(updateQueue(this.getQueueId(), data));
    promise.success(() => {
      replaceRoute('/chat/queues');
    });

    return promise;
  };

  render() {
    const { agentsLoaded, agentTeamsLoaded, queuesLoaded, queues } = this.props;

    if (!agentsLoaded || !agentTeamsLoaded || !queuesLoaded) {
      return <LoadingPage />;
    }

    return (
      <QueueForm
        {...this.props}
        returnBack={this.returnBack}
        queue={queues.get(this.getQueueId())}
        onSubmit={this.submitData}
        deleteQueue={this.deleteQueue}
      />
    );
  }
}

export default EditQueueContainer;
