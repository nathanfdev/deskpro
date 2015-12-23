import React, { Component, PropTypes } from 'react';
import createFragment from 'react-addons-create-fragment';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { DatePeriods } from 'DeskPRO/Bundle/AgentBundle/Services/DatePeriods';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';

export class DatePeriodFilter extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    stateValue: PropTypes.func.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    unsetParams: PropTypes.func.isRequired,
    setActiveItem: PropTypes.func,
    activeItem: PropTypes.object,
    filter: PropTypes.object.isRequired
  };

  renderPeriods() {
    const periods = DatePeriods.all;
    const options = {};
    for (var property in periods) {
      if (periods.hasOwnProperty(property)) {
        options[property] = <option key={property} value="">{periods[property]}</option>;
      }
    }
    return options;
  }

  render() {
    const { dispatch, setParamsAction, stateValue, filter, unsetParams, setActiveItem, activeItem } = this.props;
    const {fromParam, toParam, icon, label} = filter;
    const from = stateValue(fromParam);
    const to = stateValue(toParam);
    const isActive = Boolean(from || to);

    return (
      <FilterItem activeItem={activeItem}
                  icon={icon || 'calendar-o'}
                  label={label}
                  isActive={isActive}
                  setActiveItem={setActiveItem}
                  resetFilter={unsetParams.bind(this, [fromParam, toParam])}>

        {this.renderDateCreatedItemContent(from, to)}
        <Menu>
          <div
            className="dpw-navigation-dropdown-panel dpw-navigation-dropdown-panel-corner-left">
            <div className="dpw-navigation-dropdown-panel-content">
              <div className="dpw-navigation-dropdown-panel-content-line">
                <div className="dpw-navigation-dropdown-panel-content-full">
                  <select>
                    <option value="date_created">Created</option>
                    <option value="date_updated">Updated</option>
                    <option value="date_pulished">Published</option>
                    <option value="date_last_comment">Last comment</option>
                  </select>
                </div>
              </div>
              <div className="dpw-navigation-dropdown-panel-content-line">
                <div className="dpw-navigation-dropdown-panel-content-full">
                  <select>
                    {createFragment(this.renderPeriods())}
                  </select>
                </div>
              </div>
            </div>
          </div>
        </Menu >
      </FilterItem>
    );
  }
}