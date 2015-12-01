import React, { PropTypes } from 'react';
import { ActivePane } from './Pane/Active/ActivePane';
import { DonePane } from './Pane/Done/DonePane';

export class Controls extends React.Component {

  static propTypes = {
    isEnded: PropTypes.bool
  };

  render() {
    return this.props.isEnded ? <DonePane /> : <ActivePane />;
  }
}
