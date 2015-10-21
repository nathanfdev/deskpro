import React, { PropTypes } from 'react';
import { ProfileForm } from './Form/ProfileForm';

export class Content extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired,
    timezones: PropTypes.object.isRequired
  };

  render() {
    const { dispatch, languages, timezones } = this.props;

    return (
      <div>
        <ProfileForm dispatch={dispatch}
                     languages={languages}
                     timezones={timezones} />
      </div>
    );
  }
}
