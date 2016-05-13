import React, { Component, PropTypes } from 'react';
import createFragment from 'react-addons-create-fragment';
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
    const { setParam } = this.props;
    const filter = this.refs.filter.value;
    if (filter) {
      const value = this.refs.filterValue.value;
      setParam({ param: 'date_filter', value: { [filter]: value } });
    }
  };

  reset = () => {
    const { unsetParam } = this.props;
    unsetParam('date_filter');
  };

  renderPeriods = () => {
    const periods = DatePeriods.all;
    const options = {};
    for (const property in periods) {
      if (periods.hasOwnProperty(property)) {
        options[property] = <option key={property} value={property}>{periods[property]}</option>;
      }
    }
    return options;
  };

  render() {
    const { filter, setActiveItem, activeItem, currentParams } = this.props;
    const { icon, label } = filter;
    const value     = currentParams[filter.param];
    let filterType  = 'Select option';
    let filterValue = 'Today';
    for (const property in value) {
      if (value.hasOwnProperty(property)) {
        filterType  = property;
        filterValue = value[property];
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
        {isActive && <span className="dpw-navigation-dropdown-item-inline-info">
          {filterType} {filterValue}
        </span> }
        <Menu>
          <div className="dpw-navigation-dropdown-panel dpw-navigation-dropdown-panel-corner-left">
            <div className="dpw-navigation-dropdown-panel-content">
              <div className="dpw-navigation-dropdown-panel-content-line">
                <div className="dpw-navigation-dropdown-panel-content-full">
                  <select
                    ref="filter"
                    value={filterType}
                    onChange={this.handleChange}
                  >
                    <option value="">Select option</option>
                    <option value="period_created">Created</option>
                    <option value="period_updated">Updated</option>
                    <option value="period_published">Published</option>
                    <option value="period_last_comment">Last comment</option>
                  </select>
                </div>
              </div>
              <div className="dpw-navigation-dropdown-panel-content-line">
                <div className="dpw-navigation-dropdown-panel-content-full">
                  <select
                    ref="filterValue"
                    value={filterValue}
                    onChange={this.handleChange}
                  >
                    {createFragment(this.renderPeriods())}
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
