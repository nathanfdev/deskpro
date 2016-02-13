import React, { PropTypes } from 'react';
import { ProfileForm } from './Form/ProfileForm';
import Loader from 'react-loader';

export class Content extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired,
    timezones: PropTypes.object.isRequired,
    profile: PropTypes.object.isRequired,
    profileLoaded: PropTypes.bool.isRequired
  };

  render() {
    const { dispatch, languages, timezones, profile, profileLoaded } = this.props;

    return (
      <div>
        <Loader loaded={profileLoaded}
                opacity={0}
                width={3}>

          <ProfileForm dispatch={dispatch}
                       languages={languages}
                       timezones={timezones}
                       profile={profile} />
        </Loader>
      </div>
    );
  }
}
