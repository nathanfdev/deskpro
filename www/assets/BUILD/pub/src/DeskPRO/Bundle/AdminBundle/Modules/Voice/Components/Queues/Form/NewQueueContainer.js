import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import QueuePageForm from './QueuePageForm';
import BaseQueueFormContainer from './BaseQueueFormContainer';
import { createQueue } from '../../../Actions/queueActions';
import LoadingFormContainer from './LoadingFormContainer';

@connect()
class NewQueueContainer extends BaseQueueFormContainer {

  static propTypes = {
    dispatch: PropTypes.func
  };

  submitData = data => this.props.dispatch(createQueue(data));

  render() {
    return (
      <LoadingFormContainer
        {...this.state}
        form={QueuePageForm}
        onSubmit={this.onSubmit}
        onDelete={this.onDelete}
        onReturnBack={this.onReturnBack}
        onCancel={this.onReturnBack}
      />
    );
  }
}

export default NewQueueContainer;
