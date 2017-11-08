import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { lostConnectionSelector } from '../../../../Selectors/chat';
import { LostConnection } from './LostConnection';

@connect(state => ({
  lostConnection: lostConnectionSelector(state)
}))

export class LostConnectionContainer extends React.Component {

  static propTypes = {
    lostConnection: PropTypes.bool
  };

  render() {
    if (!this.props.lostConnection) {
      return null;
    }

    return <LostConnection />;
  }
}
export default LostConnectionContainer;
