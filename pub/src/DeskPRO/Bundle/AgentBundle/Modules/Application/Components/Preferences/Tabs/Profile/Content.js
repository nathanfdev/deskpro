import React, { PropTypes } from 'react';
import { ProfileForm } from './Form/ProfileForm';

export class Content extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    languages: PropTypes.object.isRequired
  };

  render() {
    const { dispatch, languages } = this.props;

    return (
      <div>
        <ProfileForm dispatch={dispatch} languages={languages} />
      </div>
    );
  }
}
