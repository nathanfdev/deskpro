import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';

export class DatePeriodFilter extends Component {
  static propTypes = {
    setParam:      PropTypes.func.isRequired,
    unsetParam:    PropTypes.func.isRequired,
    setActiveItem: PropTypes.func,
    activeItem:    PropTypes.object,
    currentParams: PropTypes.object.isRequired,
    filter:        PropTypes.object.isRequired
  };

  handleChange = () => {
    const { setParam, filter } = this.props;
    const value = this.refs.filterValue.value;
    if (filter.property) {
      setParam({ param: filter.property, value });
    } else if (this.refs.filter) {
      const filterProperty = this.refs.filter.value;
      if (filter) {
        setParam({ param: 'date_filter', value: { [filterProperty]: value } });
      }
    }
  };

  reset = () => {
    const { unsetParam } = this.props;
    unsetParam('date_filter');
  };

  renderFilterProperties = (filterType) => {
    const { filter } = this.props;
    return (
      <div className="dpw-navigation-dropdown-panel-content-line">
        <div className="dpw-navigation-dropdown-panel-content-full">
          <select
            ref="filter"
            value={filterType}
            onChange={this.handleChange}
          >
            <option value="">Select option</option>
            {filter.filterProperties.map(
              (property, index) => <option key={index} value={property.value}>{property.label}</option>)
            }
          </select>
        </div>
      </div>
    );
  };

  renderPeriods = () => {
    const periods = DatePeriods.all;
    const options = [<option key={0} value="">Select period</option>];
    Object.keys(periods).forEach((period) => {
      options.push(<option key={period} value={period}>{periods[period]}</option>);
    });

    return options;
  };

  render = () => {
    const { filter, setActiveItem, activeItem, currentParams } = this.props;
    const { icon, label } = filter;
    const periods = DatePeriods.all;
    let filterType  = 'Select option';
    let filterValue = 'Select period';
    const value     = currentParams[filter.param];
    if (value) {
      if (typeof value === 'string') {
        filterType = '';
        filterValue = value;
      } else {
        Object.keys(value).forEach((property) => {
          filterType = property;
          filterValue = value[property];
        });
      }
    }
    const isActive = Boolean(value);

    return (
      <FilterItem
        activeItem={activeItem}
        icon={icon || 'calendar-o'}
        label={label}
        isActive={isActive}
        setActiveItem={setActiveItem}
        resetFilter={this.reset}
      >
        {isActive
        && <span className="dpw-navigation-dropdown-item-inline-info">
          {filterType} {periods[filterValue]}
        </span>}
        <Menu>
          <div className="dpw-navigation-dropdown-panel dpw-navigation-dropdown-panel-corner-left">
            <div className="dpw-navigation-dropdown-panel-content">
              {filter.filterProperties && this.renderFilterProperties(filterType)}
              <div className="dpw-navigation-dropdown-panel-content-line">
                <div className="dpw-navigation-dropdown-panel-content-full">
                  <select
                    ref="filterValue"
                    value={filterValue}
                    onChange={this.handleChange}
                  >
                    {this.renderPeriods()}
                  </select>
                </div>
              </div>
            </div>
          </div>
        </Menu>
      </FilterItem>
    );
  }
}
