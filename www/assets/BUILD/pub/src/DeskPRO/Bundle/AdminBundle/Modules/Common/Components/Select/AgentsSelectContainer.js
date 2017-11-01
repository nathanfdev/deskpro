import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Select, RecordsChoiceWrapper } from 'DeskPRO/Component/Semantic/ReactForm';
import { allAgentsSelector } from '../../../Application/Selectors/people';

@connect(state => ({
  agents: allAgentsSelector(state)
}))
class AgentsSelectContainer extends React.Component {

  static propTypes = {
    agents: PropTypes.object
  };

  render() {
    const { agents } = this.props;

    return (
      <RecordsChoiceWrapper records={agents}>
        <Select {...this.props} clearable={false} />
      </RecordsChoiceWrapper>
    );
  }
}

export default AgentsSelectContainer;
