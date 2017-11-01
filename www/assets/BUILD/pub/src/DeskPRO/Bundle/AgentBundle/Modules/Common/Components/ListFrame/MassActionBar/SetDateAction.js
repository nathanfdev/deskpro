import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { DateTimePicker } from '../../Form/DateTime/DateTimePicker';

import { connect } from 'react-redux';
@connect()
export class SetDateAction extends Component {
  static propTypes = {
    dispatch:      PropTypes.func.isRequired,
    currentParams: PropTypes.object,
    setParams:     PropTypes.func.isRequired,
    param:         PropTypes.string.isRequired
  };

  render() {
    const { dispatch, currentParams, setParams, param } = this.props;
    return (
      <div
        className="dpw-navigation-dropdown-panel dpw-navigation-date-picker-panel dpw-navigation-dropdown-panel-corner-left"
      >
        <div className="dpw-date-picker">

          <div className="dpw-date-picker-panel-container">
            <form>
              <DateTimePicker
                label="From"
                className="dpw-date-picker-left"
                value={currentParams.get(param)}
                onChange={value => dispatch(setParams({ [param]: value }))}
              />
            </form>
          </div>
        </div>
      </div>
    );
  }
}
