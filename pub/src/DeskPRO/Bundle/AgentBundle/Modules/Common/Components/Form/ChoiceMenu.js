import React, {Component, PropTypes} from 'react';
import {QuickFilter} from './QuickFilter';

export class ChoiceMenu extends Component {

  static propTypes = {
    title: PropTypes.string.isRequired,
    children: PropTypes.any.isRequired
  };

  render() {
    return (
      <div className="dpw-navigation-dropdown-panel dpw-navigation-dropdown-panel-corner-left">

        <div className="dpw-navigation-dropdown-panel-content">

          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              <ChoiceMenuHeader title={this.props.title}/>

              <div className="dpw-departments-long-list">

                <QuickFilter/>

                <div className="dpw--popup-item-collection">
                  {this.props.children}
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

  static propTypes = {
    title: PropTypes.string.isRequired
  };

  render() {
    return (
      <div className="dpw-navigation-dropdown-mini-header">
        {this.props.title}
      </div>
    );
  }
}