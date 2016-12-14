import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import AutoAttendantForm from './AutoAttendantForm';
import BaseAutoAttendantFormContainer from './BaseAutoAttendantFormContainer';
import { loadAutoAttendants, editAutoAttendant } from '../../../Actions/autoAttendantActions';
import { allAutoAttendantsSelector } from '../../../Selectors/autoAttendant';
import LoadingContainer from './LoadingContainer';

@connect(state => ({
  autoAttendants: allAutoAttendantsSelector(state)
}))
class EditAutoAttendantContainer extends BaseAutoAttendantFormContainer {

  static propTypes = {
    dispatch:       PropTypes.func,
    autoAttendants: PropTypes.object,
    params:         PropTypes.object
  };

  componentDidMount() {
    this.props.dispatch(loadAutoAttendants());
  }

  submitData = data => this.props.dispatch(editAutoAttendant(this.getAutoAttendantId(), data));

  getAutoAttendantId() {
    return parseInt(this.props.params.autoAttendantId, 10);
  }

  render() {
    const { autoAttendants } = this.props;
    const autoAttendantId = this.getAutoAttendantId();
    const autoAttendant = autoAttendants.get(autoAttendantId);

    return (
      <LoadingContainer objectLoaded={!!autoAttendant}>
        <AutoAttendantForm
          {...this.state}
          autoAttendant={autoAttendant}
          onSubmit={this.onSubmit}
          onReturnBack={this.onReturnBack}
        />
      </LoadingContainer>
    );
  }
}

export default EditAutoAttendantContainer;
