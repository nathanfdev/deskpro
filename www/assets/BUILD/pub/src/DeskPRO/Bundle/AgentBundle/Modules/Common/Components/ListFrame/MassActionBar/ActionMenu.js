import React, { Component, PropTypes } from 'react';
import Immutable from 'immutable';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { AddLabelsContainer } from './AddLabelsContainer';
import { RemoveLabelsContainer } from './RemoveLabelsContainer';
import { SingleChoiceFilter } from '../../ListFrame/ControlBar/Filtering/SingleChoiceFilter';

import { connect } from 'react-redux';
@connect()
export class ActionMenu extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    options: PropTypes.array.isRequired,
    setParams: PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams: PropTypes.object
  };

  setParam = (params)=> {
    const {setParams, dispatch } = this.props;
    dispatch(setParams({ [params.param]: params.value }));
  };

  unsetParam(param) {
    const {resetSingleAction, dispatch } = this.props;
    dispatch(resetSingleAction(param));
  }

  stateValue = (param) => {
    const {currentParams} = this.props;
    if (currentParams) {
      if (param instanceof Array) {
        const result = [];
        param.map(item => {
          let value = currentParams.get(item);
          if (Immutable.Iterable.isIterable(value)) {
            value = value.toJS();
            value.map(item1=> {
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

  choiceOtherAction = (option, key)=> {
    const {setParams, currentParams, resetSingleAction } = this.props;

    if (option.param === 'add_labels') {
      return (
        <AddLabelsContainer key={key}
                            option={option}
                            stateValue={this.stateValue}
                            setParams={this.setParam}
                            unsetParams={this.unsetParam}/>
      );
    } else if (option.param === 'remove_labels') {
      return (
        <RemoveLabelsContainer key={key}
                               option={option}
                               stateValue={this.stateValue}
                               setParams={this.setParam}
                               unsetParams={this.unsetParam}/>
      );
    } else if (option.type === 'set_action') {
      return (
        <SingleChoiceFilter key={key}
                            filter={option}
                            state={currentParams}
                            stateValue={this.stateValue}
                            setParamsAction={setParams}
                            unsetParams={resetSingleAction}/>
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