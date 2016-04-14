import React, { Component, PropTypes } from 'react';
import { selectedSelector, paramsSelector } from '../../../../Application/Selectors/massActions';
import { cancelMassActions, submitMassActions } from '../../../../Application/Actions/massActions';
import { MassActionDropdown } from './MassActionDropdown';
import { SubmitButton } from './SubmitButton';

import { connect } from 'react-redux';
@connect(state => ({
  ids:           selectedSelector(state),
  currentParams: paramsSelector(state)
}))
export class MassActionBarContainer extends Component {
  static propTypes = {
    ids:                 PropTypes.object.isRequired,
    dispatch:            PropTypes.func.isRequired,
    reloadNavAction:     PropTypes.func.isRequired,
    loadIndicatorAction: PropTypes.func.isRequired,
    actions:             PropTypes.array.isRequired,
    content:             PropTypes.string.isRequired,
    currentParams:       PropTypes.object
  };

  submit = () => {
    const { dispatch, ids, currentParams, content, loadIndicatorAction, reloadNavAction } = this.props;
    dispatch(submitMassActions(
      {
        ids,
        content,
        reloadNavAction,

        loadIndicatorAction,
        actions: currentParams
      }));
  };

  cancel = () => {
    const { dispatch } = this.props;
    dispatch(cancelMassActions());
  };

  render() {
    const { actions, currentParams } = this.props;
    const isActive     = currentParams && currentParams.size > 0;
    const renderByType = (item, index) => {
      if (item.type === 'button') {
        return (
          <SubmitButton
            label={item.label}
            key={index}
            onClick={item.onClick}
            />
        );
      }
      return (
        <MassActionDropdown
          key={index}
          id={index}
          item={item}
          currentParams={currentParams}
          />
      );
    };

    return (
      <ul className="dpwd-navigation-dropdown-top-row-main-list">
        {actions.map((item, index) => renderByType(item, index))}
        {isActive && <li>
          <hr />
        </li>}
        {isActive &&
        <SubmitButton
          label="Go"
          onClick={this.submit}
          isActive={isActive}
          />
        }
        {isActive &&
        <SubmitButton label="Cancel"
          onClick={this.cancel}
          isActive={isActive}
          />
        }

      </ul>
    );
  }
}
