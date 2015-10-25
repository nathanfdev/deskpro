import React, { PropTypes } from 'react';
import { ProfileForm } from './Form/ProfileForm';

export class Content extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired,
    timezones: PropTypes.object.isRequired,
    profile: PropTypes.object.isRequired,
    profileStatus: PropTypes.object.isRequired
  };

  render() {
    const { dispatch, languages, timezones, profile, profileStatus } = this.props;

    if (!profileStatus.get('isDone')) {
      return (
        <div>Loading...</div>
      );
    }

    return (
      <div>
        <ProfileForm dispatch={dispatch}
                     languages={languages}
                     timezones={timezones}
                     profile={profile} />
      </div>
    );
  }
}
