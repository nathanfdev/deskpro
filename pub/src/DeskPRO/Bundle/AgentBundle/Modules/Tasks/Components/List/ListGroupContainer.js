import React, { PropTypes } from 'react';
import { connect } from 'react-redux';

@connect()
export class ListGroupContainer extends React.Component {

  static propTypes = {
    tasks: PropTypes.object
  };

  render() {
    const child = this.props.children;
    const childProps = child.props;

    return React.cloneElement(child, {
      ...childProps,
      tasks: this.props.tasks
    });
  }
}
