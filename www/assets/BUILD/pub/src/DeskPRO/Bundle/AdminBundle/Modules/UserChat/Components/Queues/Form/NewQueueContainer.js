import React from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import LoadingFormContainer from './LoadingFormContainer';
import { replaceRoute } from '../../../../../Services/history';
import { createQueue } from '../../../Actions/queueActions';

@connect()
class NewQueueContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  submitData = (data) => {
    const promise = this.props.dispatch(createQueue(data));
    promise.success(() => {
      replaceRoute('/chat/queues');
    });

    return promise;
  };

  render() {
    return <LoadingFormContainer onSubmit={this.submitData} />;
  }
}

export default NewQueueContainer;
