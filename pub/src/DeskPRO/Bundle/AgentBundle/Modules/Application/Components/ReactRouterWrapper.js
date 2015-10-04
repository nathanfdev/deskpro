import React from 'react';
import DpApp from './DpApp';

export default class ReactRouterWrapper extends React.Component {
  render() {
    return (
      <DpApp>
        {this.props.children}
      </DpApp>
    );
  }
}
