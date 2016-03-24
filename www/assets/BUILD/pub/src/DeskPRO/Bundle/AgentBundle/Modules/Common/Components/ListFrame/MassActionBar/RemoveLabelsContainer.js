import React, { Component, PropTypes } from 'react';
import { paramsSelector } from '../../../../Application/Selectors/massActions';
import { LabelsFilter }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/Filtering/LabelsFilter';

import { connect } from 'react-redux';
@connect(state => ({
  currentParams: paramsSelector(state)
}))
export class RemoveLabelsContainer extends Component {
  static propTypes = {
    currentParams: PropTypes.object.isRequired,
    setParams: PropTypes.func.isRequired,
    stateValue: PropTypes.func.isRequired,
    unsetParams: PropTypes.func.isRequired,
    option: PropTypes.object.isRequired
  };

  render() {
    const { option, stateValue, setParams, unsetParams, currentParams } = this.props;

    return (
      <LabelsFilter filter={option}
                    setParam={setParams}
                    currentParams={currentParams.toJS()}
                    icon={option.icon || 'tags'}
                    label={option.label}
                    stateValue={stateValue}
                    unsetParam={unsetParams}/>
    );
  }
}