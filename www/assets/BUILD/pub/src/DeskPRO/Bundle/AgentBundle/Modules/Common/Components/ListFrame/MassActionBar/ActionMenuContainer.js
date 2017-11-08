import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Immutable from 'immutable';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { LabelsFilter }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/Filtering/LabelsFilter';
import { SingleChoiceFilter } from '../../ListFrame/ControlBar/Filtering/SingleChoiceFilter';
import { paramsSelector } from '../../../../Application/Selectors/massActions';
import { connect } from 'react-redux';

@connect(state => ({
  currentParams: paramsSelector(state)
}))
export class ActionMenuContainer extends Component {
  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    options:           PropTypes.array.isRequired,
    setParams:         PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams:     PropTypes.object
  };

  setLabelsParam = (params) => {
    const { setParams, dispatch } = this.props;
    dispatch(setParams({ [params.param]: params.value }));
  };

  unsetLabelsParam = (param) => {
    const { resetSingleAction, dispatch } = this.props;
    dispatch(resetSingleAction(param));
  };

  stateValue = (param) => {
    const { currentParams } = this.props;
    if (currentParams) {
      if (param instanceof Array) {
        const result = [];
        param.map(item => {
          let value = currentParams.get(item);
          if (Immutable.Iterable.isIterable(value)) {
            value = value.toJS();
            value.map(item1 => {
              result.push(item1);
            });
          }
        });
        return [...new Set(result)];
      }
      let value = currentParams.get(param);
      if (Immutable.Iterable.isIterable(value)) {
        value = value.toJS();
      }
      return value;
    }
  };

  choiceOtherAction = (option, key) => {
    const { setParams, currentParams, resetSingleAction } = this.props;

    if (option.param === 'add_labels' || option.param === 'remove_labels') {
      return (
        <LabelsFilter key={key}
          filter={option}
          icon={option.icon || 'tags'}
          label={option.label}
          currentParams={currentParams.toJS()}
          stateValue={this.stateValue}
          setParam={this.setLabelsParam}
          unsetParam={this.unsetLabelsParam}
        />
      );
    } else if (option.type === 'set_action') {
      return (
        <SingleChoiceFilter key={key}
          filter={option}
          state={currentParams}
          stateValue={this.stateValue}
          setParamsAction={setParams}
          unsetParams={resetSingleAction}
        />
      );
    }
  };

  render() {
    const { options } = this.props;

    return (
      <Menu>
        {options.map((option, key) => this.choiceOtherAction(option, key))}
      </Menu>
    );
  }
}
