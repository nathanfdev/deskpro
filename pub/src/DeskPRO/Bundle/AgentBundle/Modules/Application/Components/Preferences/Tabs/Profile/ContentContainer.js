import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';
import * as ProfilesActions from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/profilesActions';
import { loadAll, allLanguagesSelector, collectionSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';
import { mySelector, myStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/profilesSelectors';

@connect(state => ({
  languages: allLanguagesSelector(state),
  timezones: collectionSelectorFactory('Timezone', 'all')(state),
  profile: mySelector(state),
  profileStatus: myStatusSelector(state)
}))
export class ContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired,
    timezones: PropTypes.object.isRequired,
    profile: PropTypes.object.isRequired,
    profileStatus: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    props.dispatch(loadAll('Timezone'));
    props.dispatch(ProfilesActions.loadMy());
  }

  render() {
    return (
      <div>
        <Content {...this.props} />
      </div>
    );
  }
}
