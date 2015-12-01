import React, { Component, PropTypes } from 'react';
import { ListFrameMenu } from '../ListFrameMenu';
import { ActionContainer } from './ActionContainer';
import { connect } from 'react-redux';

export class MassActionBar extends Component {
  static propTypes = {
    selected: PropTypes.object.isRequired,
    action: PropTypes.func.isRequired,
    actions: PropTypes.array.isRequired,
    checkbox: PropTypes.shape({
      count: PropTypes.number.isRequired,
      action: PropTypes.func.isRequired
    })
  };

  render() {
    const { checkbox, actions, action, selected } = this.props;

    return (
      <ListFrameMenu checkbox={checkbox}>
        {actions.map((item, index)=>
            <ActionContainer key={index} item={item}/>
        )}
        <li>
          <hr/>
        </li>
        <GoMassActionButton action={action} selected={selected}/>
      </ListFrameMenu>
    );
  }
}

@connect()
export class GoMassActionButton extends Component {
  static propTypes = {
    selected: PropTypes.object.isRequired,
    action: PropTypes.func.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  onClick(event) {
    event.preventDefault();
    const {action, dispatch, selected} = this.props;
    dispatch(action({ids: selected.toArray()}));
  }

  render() {
    return (
      <li>
        <span
          className="dpwd-navigation-dropdown-top-row-action-button dpwd-navigation-dropdown-top-row-action-button-flat">
          <a href="#1" className="top-row-action-button-link" onClick={this.onClick.bind(this)}>
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