import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { elementsMapSelector } from '../../../Selectors/list';

@connect(state => ({
  tasksMap: elementsMapSelector(state)
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
    const { tasksMap, item, children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      task: tasksMap.get(item.id),
      width: item.width
    });
  }
}
