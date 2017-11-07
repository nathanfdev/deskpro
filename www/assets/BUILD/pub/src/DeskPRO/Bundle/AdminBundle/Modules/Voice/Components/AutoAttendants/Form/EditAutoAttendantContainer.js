import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import AutoAttendantForm from './AutoAttendantForm';
import BaseAutoAttendantFormContainer from './BaseAutoAttendantFormContainer';
import { replaceRoute } from '../../../../../Services/history';
import { loadAutoAttendants, editAutoAttendant, deleteAutoAttendant } from '../../../Actions/autoAttendantActions';
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

  onDelete = () => {
    const { dispatch } = this.props;
    const promise = dispatch(deleteAutoAttendant(this.getAutoAttendantId()));

    promise.success(() => {
      replaceRoute('/voice_channel/auto_attendants');
    });

    return promise;
  };

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
          onDelete={this.onDelete}
        />
      </LoadingContainer>
    );
  }
}

export default EditAutoAttendantContainer;
