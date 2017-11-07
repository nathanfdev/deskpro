import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import QueueForm from './QueueForm';
import { createQueue } from '../../../Actions/queueActions';
import LoadingFormContainer from './LoadingFormContainer';

@connect()
class NewQueueModalContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    onClose:  PropTypes.func
  };

  onSubmit = (data) => {
    const { dispatch, onClose } = this.props;

    this.setState({
      saving: true,
      errors: {}
    });

    const promise = dispatch(createQueue(data));
    promise.success(() => onClose());
    promise.error((result) => {
      this.setState({
        errors: result.errors,
        saving: false
      });
    });
  };

  render() {
    return (
      <div className="page page-modal-window">
        <LoadingFormContainer
          {...this.state}
          form={QueueForm}
          onSubmit={this.onSubmit}
          onDelete={this.onDelete}
          onReturnBack={this.onReturnBack}
        />
      </div>
    );
  }
}

export default NewQueueModalContainer;
