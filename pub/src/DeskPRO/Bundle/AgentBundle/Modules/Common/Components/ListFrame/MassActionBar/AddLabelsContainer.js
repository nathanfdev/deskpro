import React, { Component, PropTypes } from 'react';
import { LabelsFilter }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/Filtering/LabelsFilter';

import { connect } from 'react-redux';
@connect()
export class AddLabelsContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    setParams: PropTypes.func.isRequired,
    unsetParams: PropTypes.func.isRequired,
    stateValue: PropTypes.func.isRequired,
    renderFilterInfo: PropTypes.func.isRequired,
    option: PropTypes.object.isRequired
  };

  render() {
    const {dispatch, option, activeItem, setActiveItem, stateValue, setParams, renderFilterInfo, unsetParams} = this.props;

    return (
      <LabelsFilter dispatch={dispatch}
                    filter={option}
                    setParamsAction={setParams}
                    activeItem={activeItem}
                    icon={option.icon || 'tags'}
                    label={option.label}
                    renderFilterInfo={renderFilterInfo}
                    setActiveItem={setActiveItem}
                    stateValue={stateValue}
                    unsetParams={unsetParams}
        />
    );
  }
}