import React from 'react';
import { connect } from 'react-redux';
import Dialpad from './Dialpad';
import { outboundNumbersSelector } from '../../../Selectors/numbers';

@connect(state => ({
  numbers: outboundNumbersSelector(state)
}))
class DialpadContainer extends React.Component {

  render() {
    return <Dialpad {...this.props} />;
  }
}

export default DialpadContainer;
