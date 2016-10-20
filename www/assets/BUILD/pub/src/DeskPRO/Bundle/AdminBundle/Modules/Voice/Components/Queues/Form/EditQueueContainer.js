import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import QueueForm from './QueueForm';
import BaseQueueFormContainer from './BaseQueueFormContainer';
import { AgentsContainer } from '../../../../Application/Components/AgentsContainer';
import { updateQueue, loadQueues } from '../../../Actions/queueActions';
import { allQueuesSelector } from '../../../Selectors/queue';

@connect(state => ({
  queues: allQueuesSelector(state)
}))
class EditQueueContainer extends BaseQueueFormContainer {

  static propTypes = {
    dispatch: PropTypes.func,
    queues:   PropTypes.object,
    params:   PropTypes.object
  };

  componentDidMount() {
    this.props.dispatch(loadQueues());
  }

  submitData = data => this.props.dispatch(updateQueue(this.getQueueId(), data));

  getQueueId() {
    return parseInt(this.props.params.queueId, 10);
  }

  render() {
    const { queues } = this.props;
    const queueId = this.getQueueId();

    if (!queues.has(queueId)) {
      return <LoadingPage />;
    }

    return (
      <AgentsContainer>
        <QueueForm
          {...this.state}
          queue={queues.get(queueId)}
          onSubmit={this.onSubmit}
          onReturnBack={this.onReturnBack}
        />
      </AgentsContainer>
    );
  }
}

export default EditQueueContainer;
