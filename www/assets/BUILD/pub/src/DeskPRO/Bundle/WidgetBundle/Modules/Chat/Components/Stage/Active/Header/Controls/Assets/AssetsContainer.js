import React from 'react';
import { connect } from 'react-redux';
import { AssetsButton } from './AssetsButton';

@connect()
export class AssetsContainer extends React.Component {

  onClick = () => {
    console.log('assets on click');
  };

  render() {
    return <AssetsButton onClick={this.onClick} />;
  }
}
