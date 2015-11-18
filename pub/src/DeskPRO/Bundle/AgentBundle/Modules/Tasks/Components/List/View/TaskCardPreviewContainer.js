import React, { PropTypes } from 'react';
import { connect } from 'react-redux';

@connect(state => ({
  tasks: state.Tasks.list.get('elements')
}))
export class TaskCardPreviewContainer extends React.Component {

  static propTypes = {
    item: PropTypes.object,
    tasks: PropTypes.object.isRequired,
    children: PropTypes.node.isRequired
  };

  render() {
    const props = this.props;
    const { tasks, item, children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,
      task: tasks.get(item.id)
    });
  }
}
