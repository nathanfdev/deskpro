import PropTypes from 'prop-types';
import React from 'react';
import NumberTargetList from './NumberTargetList';

class QueueTargetList extends React.Component {

  static propTypes = {
    queues:       PropTypes.object,
    ids:          PropTypes.array,
    onRemove:     PropTypes.func,
    displayCount: PropTypes.number
  };

  render() {
    const { ids = [], queues, onRemove, displayCount } = this.props;
    const targets = [];

    ids.forEach((id) => {
      if (queues.has(id)) {
        targets.push({ id, name: queues.get(id).get('name') });
      }
    });

    return (
      <NumberTargetList
        targets={targets}
        onRemove={onRemove}
        displayCount={displayCount}
      />
    );
  }
}

export default QueueTargetList;
