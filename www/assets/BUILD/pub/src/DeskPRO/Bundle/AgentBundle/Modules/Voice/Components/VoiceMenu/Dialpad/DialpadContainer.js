import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Dialpad from './Dialpad';
import { dialpadOpened } from '../../../Actions/clientActions';
import { outboundNumbersSelector } from '../../../Selectors/numbers';
import { outboundNumberSelector } from '../../../Selectors/client';

@connect(state => ({
  numbers:        outboundNumbersSelector(state),
  outboundNumber: outboundNumberSelector(state)
}))
class DialpadContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  componentDidMount() {
    this.props.dispatch(dialpadOpened());
  }

  componentWillReceiveProps() {
    this.props.dispatch(dialpadOpened());
  }

  render() {
    return <Dialpad {...this.props} />;
  }
}

export default DialpadContainer;
