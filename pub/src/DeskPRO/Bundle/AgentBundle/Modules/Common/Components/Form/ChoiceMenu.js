import React, {Component, PropTypes} from 'react';
import {QuickFilter} from './QuickFilter';

export class ChoiceMenu extends Component {
  render() {
    return (
      <div className="dpw-navigation-dropdown-panel dpw-navigation-dropdown-panel-corner-left">

        <div className="dpw-navigation-dropdown-panel-content">

          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              <ChoiceMenuHeader/>

              <div className="dpw-departments-long-list">

                <QuickFilter/>

                <div className="dpw--popup-item-collection">
                  <ul>
                    <li>
                      <div className="dpw--popup-item-person">
                        <span className="dpw--avatar-icon"><i className="fa fa-users"></i></span> <span
                        className="dpw-popup-item-collection-name">Department</span>
                      </div>
                    </li>

                    <li>
                      <div className="dpw--popup-item-person">
                          <span className="dpw--avatar-face"
                                style={{backgroundImage: 'url(/img/avatars/org-adidas.png)'}}></span> <span
                        className="dpw-popup-item-collection-name">Department</span>
                      </div>

                      <ul>
                        <li>
                          <div className="dpw--popup-item-person">
                            <span className="dpw--avatar-icon"><i className="fa fa-users"></i></span> <span
                            className="dpw-popup-item-collection-name">Another department</span>
                          </div>

                          <ul>
                            <li>
                              <div className="dpw--popup-item-person">
                                <span className="dpw--avatar-icon"><i className="fa fa-users"></i></span> <span
                                className="dpw-popup-item-collection-name">Longname Longnamesonn Linewrapper</span>
                              </div>
                            </li>
                            <li>
                              <div className="dpw--popup-item-person">
                                  <span className="dpw--avatar-face"
                                        style={{backgroundImage: 'url(../img/avatars/org-adidas.png)'}}></span> <span
                                className="dpw-popup-item-collection-name">Department</span>
                              </div>
                            </li>
                          </ul>
                        </li>

                        <li>
                          <div className="dpw--popup-item-person">
                              <span className="dpw--avatar-face"
                                    style={{backgroundImage: 'url(../img/avatars/org-adidas.png)'}}></span> <span
                            className="dpw-popup-item-collection-name">Longname Longnamesonn Linewrapper</span>
                          </div>
                        </li>

                        <li>
                          <div className="dpw--popup-item-person">
                              <span className="dpw--avatar-face"
                                    style={{backgroundImage: 'url(../img/avatars/avatar2.png)'}}></span> <span
                            className="dpw-popup-item-collection-name">Longname Longnamesonn Linewrapper</span>
                          </div>
                        </li>

                        <li>
                          <div className="dpw--popup-item-person">
                              <span className="dpw--avatar-face"
                                    style={{backgroundImage: 'url(../img/avatars/avatar2.png)'}}></span> <span
                            className="dpw-popup-item-collection-name">Longname Longnamesonn Linewrapper</span>
                          </div>
                        </li>
                      </ul>
                    </li>

                    <li>
                      <div className="dpw--popup-item-person">
                          <span className="dpw--avatar-face"
                                style={{backgroundImage: 'url(../img/avatars/avatar3.png)'}}></span> <span
                        className="dpw-popup-item-collection-name">Christine Rogers</span>
                      </div>
                    </li>

                    <li>
                      <div className="dpw--popup-item-person">
                          <span className="dpw--avatar-face"
                                style={{backgroundImage: 'url(../img/avatars/avatar4.png)'}}></span> <span
                        className="dpw-popup-item-collection-name">Dave Sanders</span>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export class ChoiceMenuHeader extends Component {
  render() {
    return (
      <div className="dpw-navigation-dropdown-mini-header">
        Remove Labels
      </div>
    );
  }
}