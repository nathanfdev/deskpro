import React, { Component, PropTypes } from 'react';
import { selectedSelector, paramsSelector } from '../../../../Application/Selectors/massActions';
import { cancelMassActions, setMassActionsParams, resetParam, submitMassActions } from '../../../../Application/Actions/massActions';
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
    reloadNavAction: PropTypes.func.isRequired,
    loadIndicatorAction: PropTypes.func.isRequired,
    actions: PropTypes.array.isRequired,
    jobType: PropTypes.string.isRequired,
    content: PropTypes.string.isRequired,
    currentParams: PropTypes.object
  };

  submit(jobType) {
    const { dispatch, selected, currentParams, content, loadIndicatorAction, reloadNavAction } = this.props;
    dispatch(submitMassActions(
      {
        jobType: jobType,
        reloadNavAction: reloadNavAction,
        loadIndicatorAction: loadIndicatorAction,
        params: { ids: selected, content: content, actions: currentParams }
      }));
  }

  cancel() {
    const { dispatch } = this.props;
    dispatch(cancelMassActions());
  }

  render() {
    const { actions, currentParams, jobType } = this.props;
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
                                   onClick={this.submit.bind(this, jobType)}
                                   isActive={isActive}/>
        }
        {isActive && <SubmitButton label="Cancel"
                                   onClick={this.cancel.bind(this)}
                                   isActive={isActive}/>}

      </ul>
    );
  }
}
