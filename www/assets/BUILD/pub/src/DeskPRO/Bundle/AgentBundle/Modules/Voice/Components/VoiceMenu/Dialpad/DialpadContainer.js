import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
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

  onMakeCall = (callFrom, callTo) => {
    if (storageAvailable('localStorage')) {
      localStorage.setItem('dpAgent.voice.lastCallFrom', callFrom);
    }

    this.props.dispatch(makeOutboundCall(callFrom, callTo));
  };

  onSearchPerson = searchString => this.props.dispatch(searchPerson(searchString));

  render() {
    let lastCallFrom = null;
    if (storageAvailable('localStorage')) {
      lastCallFrom = parseInt(localStorage.getItem('dpAgent.voice.lastCallFrom'), 10);
    }

    return (
      <Dialpad
        {...this.props}
        lastCallFrom={lastCallFrom}
        onMakeCall={this.onMakeCall}
        onSearchPerson={this.onSearchPerson}
      />
    );
  }
}

export default DialpadContainer;
