import React, { Component, PropTypes } from 'react';
import { selectedSelector, paramsSelector } from '../../../../Application/Selectors/massActions';
import { cancelMassActions, setMassActionsParams, resetParam } from '../../../../Application/Actions/massActions';
import { ActionContainer } from './ActionContainer';
import { SubmitButton } from './SubmitButton';

import { connect } from 'react-redux';
@connect(state => ({
  selected: selectedSelector(state),
  currentParams: paramsSelector(state)
}))
export class MassActionBarContainer extends Component {
  static propTypes = {
    selected: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    submitAction: PropTypes.func.isRequired,
    actions: PropTypes.array.isRequired,
    currentParams: PropTypes.object
  };

  cancelMassActions() {
    const { dispatch } = this.props;
    dispatch(cancelMassActions());
  }

  render() {
    const { actions, submitAction, currentParams } = this.props;
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
                           setParams={setMassActionsParams}
                           resetSingleAction={resetParam}
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
                                   onClick={this.cancelMassActions.bind(this)}
                                   isActive={isActive}/>}

      </ul>
    );
  }
}
