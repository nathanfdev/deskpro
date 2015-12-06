import React, { Component, PropTypes } from 'react';
import { ListFrameMenu } from '../ListFrameMenu';
import { ActionContainer } from './ActionContainer';
import classNames from 'classnames';
import { connect } from 'react-redux';

export class MassActionBar extends Component {
  static propTypes = {
    selected: PropTypes.object.isRequired,
    action: PropTypes.func.isRequired,
    resetAction: PropTypes.func.isRequired,
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
    const { checkbox, actions, action, selected, setParams, currentParams, resetAction, resetSingleAction } = this.props;
    const isActive = currentParams && currentParams.size > 0;

    return (
      <ListFrameMenu checkbox={checkbox}>
        {actions.map((item, index) =>
          <ActionContainer key={index} id={index}
                           item={item}
                           setParams={setParams}
                           resetSingleAction={resetSingleAction}
                           currentParams={currentParams}/>)}
        {isActive && <li>
          <hr/>
        </li>}
        {isActive && <GoMassActionButton action={action}
                                         selected={selected}
                                         isActive={isActive}/>
        }
        {isActive && <ResetMassActionButton resetAction={resetAction} isActive={isActive}/>}

      </ListFrameMenu>
    );
  }
}

export class GoMassActionButton extends Component {
  static propTypes = {
    selected: PropTypes.object.isRequired,
    isActive: PropTypes.bool,
    action: PropTypes.func.isRequired
  };

  clickHandler(event) {
    event.preventDefault();
    if (this.props.isActive) {
      const {action, selected} = this.props;
      action(selected.toArray());
    }
  }

  render() {
    const classes = classNames('top-row-action-button-link', {
      'has-value': this.props.isActive,
      'disabled': !this.props.isActive
    });

    return (
      <li className="">
        <span
          className="dpwd-navigation-dropdown-top-row-action-button dpwd-navigation-dropdown-top-row-action-button-flat">
          <a href="" className={classes} onClick={this.clickHandler.bind(this)}>
            <span
              className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">
              Go
            </span>
          </a>
        </span>
      </li>
    );
  }
}

@connect()
export class ResetMassActionButton extends Component {
  static propTypes = {
    resetAction: PropTypes.func.isRequired,
    dispatch: PropTypes.func.isRequired,
    isActive: PropTypes.bool
  };

  clickHandler(event) {
    event.preventDefault();
    if (this.props.isActive) {
      const {dispatch, resetAction} = this.props;
      dispatch(resetAction());
    }
  }

  render() {
    const classes = classNames('top-row-action-button-link', {
      'has-value': this.props.isActive,
      'disabled': !this.props.isActive
    });

    return (
      <li className="">
        <span
          className="dpwd-navigation-dropdown-top-row-action-button dpwd-navigation-dropdown-top-row-action-button-flat">
          <a href="" className={classes} onClick={this.clickHandler.bind(this)}>
            <span
              className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">
              Cancel
            </span>
          </a>
        </span>
      </li>
    );
  }
}
