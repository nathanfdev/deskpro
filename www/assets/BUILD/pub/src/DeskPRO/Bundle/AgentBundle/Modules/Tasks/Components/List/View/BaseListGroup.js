import PropTypes from 'prop-types';
import React from 'react';
import { pureRender } from 'Ampliflux';

@pureRender
export class BaseListGroup extends React.Component {

  static propTypes = {
    group:             PropTypes.object.isRequired,
    isOver:            PropTypes.bool,
    connectDropTarget: PropTypes.func.isRequired
  };

  render() {
    return <div />;
  }
}
