import React from 'react';
import { connect } from 'react-redux';
import AutoAttendantForm from './AutoAttendantForm';
import BaseAutoAttendantFormContainer from './BaseAutoAttendantFormContainer';
import { createAutoAttendant } from '../../../Actions/autoAttendantActions';
import LoadingContainer from './LoadingContainer';

@connect()
class NewAutoAttendantContainer extends BaseAutoAttendantFormContainer {

  submitData = data => this.props.dispatch(createAutoAttendant(data));

  render() {
    return (
      <LoadingContainer>
        <AutoAttendantForm
          {...this.state}
          onSubmit={this.onSubmit}
          onReturnBack={this.onReturnBack}
        />
      </LoadingContainer>
    );
  }
}

export default NewAutoAttendantContainer;
