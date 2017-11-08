import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import LoadingPage from 'DeskPRO/Bundle/AdminBundle/Modules/Common/Components/LoadingPage';
import AutoAttendantList from './AutoAttendantList';
import { loadAutoAttendants } from '../../../Actions/autoAttendantActions';
import { allAutoAttendantsSelector, isAutoAttendantsLoadedSelector } from '../../../Selectors/autoAttendant';
import { replaceRoute } from '../../../../../Services/history';

@connect(state => ({
  autoAttendants:       allAutoAttendantsSelector(state),
  autoAttendantsLoaded: isAutoAttendantsLoadedSelector(state)
}))
class AutoAttendantListContainer extends React.Component {

  static propTypes = {
    autoAttendantsLoaded: PropTypes.bool,
    dispatch:             PropTypes.func
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadAutoAttendants());
  }

  onAddNew = () => {
    replaceRoute('/voice_channel/auto_attendants/new');
  };

  onEdit = (autoAttendant) => {
    replaceRoute(`/voice_channel/auto_attendants/${autoAttendant.get('id')}`);
  };

  render() {
    const { autoAttendantsLoaded } = this.props;
    if (!autoAttendantsLoaded) {
      return <LoadingPage />;
    }

    return (
      <AutoAttendantList
        {...this.props}
        onAddNew={this.onAddNew}
        onEdit={this.onEdit}
      />
    );
  }
}

export default AutoAttendantListContainer;
