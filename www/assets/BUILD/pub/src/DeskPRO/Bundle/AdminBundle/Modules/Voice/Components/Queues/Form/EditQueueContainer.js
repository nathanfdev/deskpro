import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import QueuePageForm from './QueuePageForm';
import BaseQueueFormContainer from './BaseQueueFormContainer';
import { updateQueue, deleteQueue } from '../../../Actions/queueActions';
import { replaceRoute } from '../../../../../Services/history';
import LoadingFormContainer from './LoadingFormContainer';

@connect()
class EditQueueContainer extends BaseQueueFormContainer {

  static propTypes = {
    dispatch: PropTypes.func,
    params:   PropTypes.object
  };

  onDelete = () => {
    const { dispatch } = this.props;
    const promise = dispatch(deleteQueue(this.getQueueId()));

    promise.success(() => {
      replaceRoute('/voice_channel/queues');
    });

    return promise;
  };

  submitData = data => this.props.dispatch(updateQueue(this.getQueueId(), data));
  getQueueId = () => parseInt(this.props.params.queueId, 10);

  render() {
    return (
      <LoadingFormContainer
        {...this.state}
        form={QueuePageForm}
        queueId={this.getQueueId()}
        onSubmit={this.onSubmit}
        onDelete={this.onDelete}
        onReturnBack={this.onReturnBack}
        onCancel={this.onReturnBack}
      />
    );
  }
}

export default EditQueueContainer;
