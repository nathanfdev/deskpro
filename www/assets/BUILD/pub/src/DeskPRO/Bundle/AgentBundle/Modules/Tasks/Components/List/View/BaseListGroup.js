import React, { PropTypes } from 'react';

export class BaseListGroup extends React.Component {

  static propTypes = {
    group:             PropTypes.object.isRequired,
    isOver:            PropTypes.bool,
    connectDropTarget: PropTypes.func.isRequired,
    onUpdate:          PropTypes.func
  };

  hasElements = () => this.props.group.get('elements').size > 0;
}
