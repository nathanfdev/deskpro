import PropTypes from 'prop-types';
import React, { Component } from 'react';
import classNames from 'classnames';
import { QuickFilter } from './QuickFilter';
import { RadioOption } from './RadioOption';

import { connect } from 'react-redux';
@connect()
export class SingleChoicePanelContainer extends Component {
  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    setParams:         PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams:     PropTypes.object,
    item:              PropTypes.object.isRequired,
    depth:             PropTypes.bool
  };

  onClick = (param, value, isActive) => {
    const { dispatch, resetSingleAction, setParams } = this.props;
    if (isActive) {
      dispatch(resetSingleAction(param));
    } else {
      dispatch(setParams({ [param]: value }));
    }
  };

  render() {
    const { item, currentParams, depth } = this.props;
    const renderNested = (nested) => {
      if (!nested || !nested.length) {
        return <span />;
      }

      return (
        <ul>
          {nested.map((option, index) =>
                        <RadioOption
                          key={index}
                          isActive={currentParams && currentParams.get(option.param) === option.value}
                          value={option.value}
                          param={option.param}
                          label={option.label}
                          onClick={this.onClick}
                        />
          )}
        </ul>
      );
    };

    const classes = classNames('dpw-navigation-dropdown-panel', { 'dpw-navigation-dropdown-panel-corner-left': depth });

    return (
      <div className={classes} style={{ width: '250px' }}>
        <div className="dpw-navigation-dropdown-panel-content">
          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              {item.quickFilter && <QuickFilter />}
            </div>
          </div>
          <div className="dpw-navigation-dropdown-panel-content-line">
            <div className="dpw-navigation-dropdown-panel-content-full">
              <div className="dpw--popup-item-collection">
                <ul>
                  {item.options.map((option, index) =>
                                      <RadioOption
                                        key={index}
                                        isActive={currentParams && currentParams.get(item.param) === option.value}
                                        value={option.value}
                                        label={option.label}
                                        param={item.param}
                                        onClick={this.onClick}
                                      >
                                        {renderNested(option.nested)}
                                      </RadioOption>
                  )}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
