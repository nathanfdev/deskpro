import React, { Component, PropTypes } from 'react';
import Immutable from 'immutable';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { AddLabelsContainer } from './AddLabelsContainer';
import { RemoveLabelsContainer } from './RemoveLabelsContainer';

export class ActionMenu extends Component {
  static propTypes = {
    options: PropTypes.array.isRequired,
    setParams: PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams: PropTypes.object
  };

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
                            setParams={setParams}
                            currentParams={currentParams}
                            stateValue={this.stateValue}
                            unsetParams={resetSingleAction}/>
      );
    } else if (option.param === 'remove_labels') {
      return (
        <RemoveLabelsContainer key={key}
                               option={option}
                               setParams={setParams}
                               currentParams={currentParams}
                               stateValue={this.stateValue}
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