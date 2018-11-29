import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import LoadingFormContainer from './LoadingFormContainer';
import { replaceRoute } from '../../../../../Services/history';
import { updateQueue, deleteQueue } from '../../../Actions/queueActions';

@connect()
class EditQueueContainer extends React.Component {

  static propTypes = {
    params:   PropTypes.object,
    dispatch: PropTypes.func
  };

  getQueueId = () => parseInt(this.props.params.queueId, 10);
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
    return (
      <LoadingFormContainer
        queueId={this.getQueueId()}
        onSubmit={this.submitData}
        deleteQueue={this.deleteQueue}
      />
    );
  }
}

export default EditQueueContainer;
