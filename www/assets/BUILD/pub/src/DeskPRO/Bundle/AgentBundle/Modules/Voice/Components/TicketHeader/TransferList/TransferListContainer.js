import React from 'react';
import { connect } from 'react-redux';
import TransferList from './TransferList';

@connect()
class TransferListContainer extends React.Component {

  render() {
    return <TransferList {...this.props} />;
  }
}

export default TransferListContainer;
