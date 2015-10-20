import React, { PropTypes } from 'react';
import { ProfileForm } from './Form/ProfileForm';

export class Content extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  render() {
    return (
      <div>
        <ProfileForm dispatch={this.props.dispatch} />
      </div>
    );
  }
}
