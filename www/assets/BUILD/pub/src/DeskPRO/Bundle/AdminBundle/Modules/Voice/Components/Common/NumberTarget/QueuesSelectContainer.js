import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Select, RecordsChoiceWrapper } from 'DeskPRO/Component/Semantic/ReactForm';
import { allQueuesSelector } from '../../../Selectors/queue';

@connect(state => ({
  queues: allQueuesSelector(state)
}))
class QueuesSelectContainer extends React.Component {

  static propTypes = {
    queues: PropTypes.object
  };

  render() {
    const { queues } = this.props;

    return (
      <RecordsChoiceWrapper records={queues}>
        <Select {...this.props} clearable={false} />
      </RecordsChoiceWrapper>
    );
  }
}

export default QueuesSelectContainer;
