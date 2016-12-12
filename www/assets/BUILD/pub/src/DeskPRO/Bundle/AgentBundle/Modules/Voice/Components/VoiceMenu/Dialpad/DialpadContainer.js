import React from 'react';
import { connect } from 'react-redux';
import Dialpad from './Dialpad';
import { allNumbersSelector, isNumbersLoadedSelector } from '../../../Selectors/numbers';

@connect(state => ({
  callFromNumbers:       allNumbersSelector(state),
  callFromNumbersLoaded: isNumbersLoadedSelector(state)
}))
class DialpadContainer extends React.Component {

  render() {
    return <Dialpad {...this.props} />;
  }
}

export default DialpadContainer;
