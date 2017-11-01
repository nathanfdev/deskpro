import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { QuickFilter } from './QuickFilter';
import classNames from 'classnames';

export class ChoiceMenu extends Component {

  static propTypes = {
    title:       PropTypes.string,
    quickFilter: PropTypes.bool,
    submenu:     PropTypes.bool,
    children:    PropTypes.any.isRequired
  };

  render() {
    const { title, quickFilter, children, submenu } = this.props;
    const classes = classNames('dpw-navigation-dropdown-panel', { 'dpw-navigation-dropdown-panel-corner-left': submenu });
    return (
      <div className={classes}>
        <div className="dpw-navigation-dropdown-panel-content">
          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              {title && <ChoiceMenuHeader title={title} />}
              <div className="dpw-departments-long-list">
                {quickFilter && <QuickFilter />}
                <div className="dpw--popup-item-collection">
                  {children}
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
