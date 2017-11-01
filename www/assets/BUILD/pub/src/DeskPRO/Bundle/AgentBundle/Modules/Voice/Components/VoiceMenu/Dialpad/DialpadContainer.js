import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import Dialpad from './Dialpad';
import { dialpadOpened, makeOutboundCall, searchPerson } from '../../../Actions/clientActions';
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

  onMakeCall = (callFrom, callTo) => this.props.dispatch(makeOutboundCall(callFrom, callTo));
  onSearchPerson = searchString => this.props.dispatch(searchPerson(searchString));

  render() {
    return (
      <Dialpad
        {...this.props}
        onMakeCall={this.onMakeCall}
        onSearchPerson={this.onSearchPerson}
      />
    );
  }
}

export default DialpadContainer;
