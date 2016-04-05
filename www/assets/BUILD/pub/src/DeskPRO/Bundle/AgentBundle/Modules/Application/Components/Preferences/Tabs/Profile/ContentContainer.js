import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';
import { loadAll, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { loadMyProfile, myProfileSelector, isMyProfileLoadedSelector }
  from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/profile';

@connect(state => ({
  languages: allSelectorFactory('Language')(state),
  timezones: allSelectorFactory('Timezone')(state),
  profile: myProfileSelector(state),
  profileLoaded: isMyProfileLoadedSelector(state)
}))
export class ContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentWillMount() {
    const {dispatch} = this.props;

    dispatch(loadAll('Timezone'));
    dispatch(loadMyProfile());
  }

  render() {
    return (
      <div>
        <Content {...this.props} />
      </div>
    );
  }
}
