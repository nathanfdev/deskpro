import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';

@connect()

export class HtmlReplyActionContainer extends Component {
  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    setParams:         PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams:     PropTypes.object
  };

  render() {
    return (<div>Example</div>);
  }
}
