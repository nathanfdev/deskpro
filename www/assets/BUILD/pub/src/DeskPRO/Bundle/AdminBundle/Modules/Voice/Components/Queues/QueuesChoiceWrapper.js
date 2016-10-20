import React, { PropTypes } from 'react';
import Immutable from 'immutable';

class QueuesChoiceWrapper extends React.Component {

  static propTypes = {
    queues:   PropTypes.object,
    children: PropTypes.node
  };

  render() {
    const { children, queues = Immutable.fromJS([]) } = this.props;
    const choices = queues.map(queue => ({
      value: queue.get('id'),
      label: queue.get('name')
    })).toArray();

    return React.cloneElement(children, { ...this.props, ...children.props, choices });
  }
}

export default QueuesChoiceWrapper;
