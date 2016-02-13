import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { tasksSelector } from '../../../Selectors/recordStores';

@connect(state => ({
  tasks: tasksSelector(state)
}))
export class TaskCardPreviewContainer extends React.Component {

  static propTypes = {
    item: PropTypes.shape({
      id: PropTypes.number.isRequired,
      width: PropTypes.number
    }),
    tasksMap: PropTypes.object.isRequired,
    children: PropTypes.node.isRequired
  };

  render() {
    const props = this.props;
    const { tasks, item, children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      task: tasks.get(item.id),
      width: item.width
    });
  }
}
