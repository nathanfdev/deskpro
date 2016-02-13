import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { WelcomeBack } from './WelcomeBack';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';

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
