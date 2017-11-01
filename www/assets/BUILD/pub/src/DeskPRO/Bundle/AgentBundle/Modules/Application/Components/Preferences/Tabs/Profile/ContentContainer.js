import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { Content } from './Content';
import { loadAll, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import {
  loadMyProfile, myProfileSelector, isMyProfileLoadedSelector
}
  from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/profile';

@connect(state => ({
  languages:     allSelectorFactory('Language')(state),
  timezones:     allSelectorFactory('Timezone')(state),
  profile:       myProfileSelector(state),
  profileLoaded: isMyProfileLoadedSelector(state)
}))
export class ContentContainer extends React.Component {

  static propTypes = {
    dispatch:      PropTypes.func.isRequired,
    profile:       PropTypes.object,
    profileLoaded: PropTypes.bool.isRequired
  };

  componentWillMount() {
    const { dispatch, profileLoaded } = this.props;

    dispatch(loadAll('Timezone'));
    if (!profileLoaded) {
      dispatch(loadMyProfile());
    }
  }

  render() {
    const { profileLoaded } = this.props;
    return (
      <div>
        <LoadIndicator
          loaded={profileLoaded}
          opacity={0}
          width={3}
        >
          {profileLoaded && <Content {...this.props} />}
        </LoadIndicator>
      </div>
    );
  }
}
