import React from 'react';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';

export class Controls extends React.Component {

  render() {
    return (
      <ListFrameMenu ref="ticketControlBar">
        <li>
          <a href="#" ref="orderButton" className="dpwd-navigation-dropdown-top-row-button">
            <span className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">Order by:</span>
            <span className="dpwd-navigation-dropdown-top-row-button-text">&nbsp;
              <span className="focus"></span>
              <span className="down">
                <i className="fa fa-caret-down"/></span>
            </span>
          </a>
        </li>
        <li>
          <a href="#" className="dpwd-navigation-dropdown-top-row-button">
            <span className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">Filter by:</span>
            <span className="dpwd-navigation-dropdown-top-row-button-text">&nbsp;
              <span className="focus">12</span>
              <span className="down"> Completed <i className="fa fa-caret-down"/></span>
            </span>
          </a>
        </li>
        <li>
          <a href="#" className="dpwd-navigation-dropdown-top-row-button">
            <span className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">View:</span>
            <span className="dpwd-navigation-dropdown-top-row-button-text">&nbsp;
              <span className="multi task-list-view-switcher">
                <span className="multi-down"> <i className="fa fa-caret-down"/></span>
              </span>
            </span>
          </a>

        </li>
      </ListFrameMenu>
    );
  }
}
