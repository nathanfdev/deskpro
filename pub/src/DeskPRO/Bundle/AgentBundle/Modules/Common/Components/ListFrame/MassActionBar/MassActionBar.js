import React, { Component, PropTypes } from 'react';
import { ActionContainer } from './ActionContainer';
import { SubmitButton } from './SubmitButton';

export class MassActionBar extends Component {
  static propTypes = {
    selected: PropTypes.object.isRequired,
    submitAction: PropTypes.func.isRequired,
    cancelAction: PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    setParams: PropTypes.func.isRequired,
    actions: PropTypes.array.isRequired,
    currentParams: PropTypes.object,
    checkbox: PropTypes.shape({
      count: PropTypes.number.isRequired,
      action: PropTypes.func.isRequired
    })
  };

  render() {
    const { actions, submitAction, setParams, currentParams, cancelAction, resetSingleAction } = this.props;
    const isActive = currentParams && currentParams.size > 0;
    const renderByType = (item, index)=> {
      if (item.type === 'button') {
        return (
          <SubmitButton label={item.label} key={index}
                        onClick={item.onClick}/>
        );
      }
      if (item.type === 'action' || item.type === 'menu') {
        return (
          <ActionContainer key={index} id={index}
                           item={item}
                           setParams={setParams}
                           resetSingleAction={resetSingleAction}
                           currentParams={currentParams}/>
        );
      }
    };

    return (
      <ul className="dpwd-navigation-dropdown-top-row-main-list">
        {actions.map((item, index) => renderByType(item, index))}
        {isActive && <li>
          <hr/>
        </li>}
        {isActive && <SubmitButton label="Go"
                                   onClick={submitAction}
                                   isActive={isActive}/>
        }
        {isActive && <SubmitButton label="Cancel"
                                   onClick={cancelAction}
                                   isActive={isActive}/>}

      </ul>
    );
  }
}
