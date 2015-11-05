import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';
import * as TimezonesActions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Actions/timezonesActions';
import * as ProfilesActions from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/profilesActions';
import { languagesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Selectors/languagesSelectors';
import { timezonesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Selectors/timezonesSelectors';
import { mySelector, myStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/profilesSelectors';

@connect(state => ({
  languages: languagesSelector(state),
  timezones: timezonesSelector(state),
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

    props.dispatch(TimezonesActions.loadAll());
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
