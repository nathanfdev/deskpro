import React, { PropTypes } from 'react';
import { DpApp } from './DpApp';

export class ReactRouterWrapper extends React.Component {

  static propTypes = {
    children: PropTypes.object.isRequired
  };

  render() {
    return (
      <DpApp>
        {this.props.children}
      </DpApp>
    );
  }
}
