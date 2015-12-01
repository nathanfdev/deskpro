import React, { Component, PropTypes } from 'react';
import { ListFrameMenu } from '../ListFrameMenu';
import { ActionContainer } from './ActionContainer';

export class MassActionBar extends Component {
  static propTypes = {
    checkbox: PropTypes.shape({
      count: PropTypes.number.isRequired,
      action: PropTypes.func.isRequired
    })
  };

  render() {
    const { checkbox, actions } = this.props;

    return (
      <ListFrameMenu checkbox={checkbox}>
        {actions.map((item, index)=>
            <ActionContainer key={index} item={item}/>
        )}
        <li>
          <hr/>
        </li>
        <li>
            <span
              className="dpwd-navigation-dropdown-top-row-action-button dpwd-navigation-dropdown-top-row-action-button-flat">
              <a href="#1" className="top-row-action-button-link">
                <span
                  className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">Go</span>
              </a>
            </span>
        </li>
      </ListFrameMenu>
    );
  }
}
