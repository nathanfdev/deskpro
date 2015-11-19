import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

@connect(state => ({
  tasks: state.Tasks.list.get('elements')
}))
export class TaskCardPreviewContainer extends React.Component {

  static propTypes = {
    item: PropTypes.shape({
      id: PropTypes.number.isRequired,
      width: PropTypes.number
    }),
    tasks: PropTypes.object.isRequired,
    children: PropTypes.node.isRequired
  };

  render() {
    const props = this.props;
    const { tasks, item, children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      task: mapKeyedFromArray(tasks, 'id').get(item.id),
      width: item.width
    });
  }
}
