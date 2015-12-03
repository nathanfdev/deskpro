import React from 'react';
import { AssetsButton } from './AssetsButton';

export class AssetsContainer extends React.Component {

  onClick = () => {
    console.log('assets on click');
  };

  render() {
    return <AssetsButton onClick={this.onClick} />;
  }
}
