import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Select, RecordsChoiceWrapper } from 'DeskPRO/Component/Semantic/ReactForm';
import { voicePeopleSelector } from '../../../../Application/Selectors/people';

@connect(state => ({
  agents: voicePeopleSelector(state)
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
