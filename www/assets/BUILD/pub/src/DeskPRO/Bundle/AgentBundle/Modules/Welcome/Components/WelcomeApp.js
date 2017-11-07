import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { WelcomeBack } from './WelcomeBack';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';

@connect(state => ({
  user: meSelector(state)
}))
export class WelcomeApp extends React.Component {

  static propTypes = {
    user: PropTypes.object.isRequired
  };

  render() {
    const { user } = this.props;

    return (
      <WelcomeBack user={user} />
    );
  }
}
