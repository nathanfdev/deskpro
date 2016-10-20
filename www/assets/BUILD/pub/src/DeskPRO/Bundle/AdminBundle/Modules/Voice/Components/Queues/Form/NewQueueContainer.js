import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import QueueForm from './QueueForm';
import BaseQueueFormContainer from './BaseQueueFormContainer';
import { createQueue } from '../../../Actions/queueActions';
import { AgentsContainer } from '../../../../Application/Components/AgentsContainer';

@connect()
class NewQueueContainer extends BaseQueueFormContainer {

  static propTypes = {
    dispatch: PropTypes.func
  };

  submitData = data => this.props.dispatch(createQueue(data));

  render() {
    return (
      <AgentsContainer>
        <QueueForm
          {...this.state}
          onSubmit={this.onSubmit}
          onReturnBack={this.onReturnBack}
        />
      </AgentsContainer>
    );
  }
}

export default NewQueueContainer;
