import PropTypes from 'prop-types';
import React from 'react';
import { ProfileForm } from './Form/ProfileForm';

export class Content extends React.Component {

  static propTypes = {
    dispatch:  PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired,
    timezones: PropTypes.object.isRequired,
    profile:   PropTypes.object.isRequired
  };

  render() {
    const { dispatch, languages, timezones, profile } = this.props;

    return (
      <div>
        <ProfileForm
          dispatch={dispatch}
          languages={languages}
          timezones={timezones}
          profile={profile}
        />
      </div>
    );
  }
}
