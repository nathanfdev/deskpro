import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Moment from 'moment';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';
import { DateTimePicker } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTime/DateTimePicker';

export class DateFilter extends Component {
  static propTypes = {
    setParam:      PropTypes.func.isRequired,
    currentParams: PropTypes.object.isRequired,
    unsetParam:    PropTypes.func.isRequired,
    setActiveItem: PropTypes.func,
    activeItem:    PropTypes.object,
    filter:        PropTypes.object.isRequired
  };

  reset = () => {
    const { filter, unsetParam } = this.props;
    unsetParam([filter.fromParam, filter.toParam]);
  };

  renderDateCreatedItemContent = (from, to) => {
    if (from || to) {
      return (
        <span className="dpw-navigation-dropdown-item-inline-info">
          {from ? Moment(from).format('DD/MM/YYYY') : '...'} - {to ? Moment(to).format('DD/MM/YYYY') : '...'}
        </span>
      );
    }
    return null;
  };

  render() {
    const { currentParams, filter, setParam, activeItem } = this.props;
    const { fromParam, toParam, icon, label } = filter;
    const from     = currentParams[fromParam];
    const to       = currentParams[toParam];
    const isActive = Boolean(from || to);
    return (
      <FilterItem
        activeItem={activeItem}
        icon={icon || 'calendar-o'}
        label={label}
        isActive={isActive}
        resetFilter={this.reset}
      >
        {this.renderDateCreatedItemContent(from, to)}
        <Menu>
          <div className="dpw-navigation-dropdown-panel dpw-navigation-date-picker-panel dpw-navigation-dropdown-panel-corner-left">
            <div className="dpw-date-picker">

              <div className="dpw-date-picker-panel-container">
                <form>
                  <DateTimePicker
                    label="From"
                    className="dpw-date-picker-left"
                    value={from}
                    onChange={value => setParam({ param: [fromParam], value })}
                    stopPropagationOnClose
                  />
                  <DateTimePicker
                    label="To"
                    className="dpw-date-picker-right"
                    value={to}
                    onChange={value => setParam({ param: [toParam], value })}
                    stopPropagationOnClose
                  />
                </form>
              </div>
            </div>
          </div>
        </Menu>
      </FilterItem>
    );
  }
}
