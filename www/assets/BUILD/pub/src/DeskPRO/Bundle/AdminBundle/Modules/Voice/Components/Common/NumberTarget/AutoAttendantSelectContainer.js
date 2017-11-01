import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Select, RecordsChoiceWrapper } from 'DeskPRO/Component/Semantic/ReactForm';
import { allAutoAttendantsSelector } from '../../../Selectors/autoAttendant';

@connect(state => ({
  autoAttendants: allAutoAttendantsSelector(state)
}))
class AutoAttendantSelectContainer extends React.Component {

  static propTypes = {
    autoAttendants: PropTypes.object,
    autoAttendant:  PropTypes.object
  };

  render() {
    const { autoAttendant, autoAttendants } = this.props;

    return (
      <RecordsChoiceWrapper records={autoAttendants.filter(record => record !== autoAttendant)}>
        <Select {...this.props} clearable={false} />
      </RecordsChoiceWrapper>
    );
  }
}

export default AutoAttendantSelectContainer;
