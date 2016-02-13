import React, { Component, PropTypes } from 'react';
import Moment from 'moment';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';
import { DateTimePicker } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTime/DateTimePicker';

export class DateFilter extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    stateValue: PropTypes.func.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    unsetParams: PropTypes.func.isRequired,
    setActiveItem: PropTypes.func,
    activeItem: PropTypes.object,
    filter: PropTypes.object.isRequired
  };

  renderDateCreatedItemContent = (from, to) => {
    if (from || to) {
      return (
        <span className="dpw-navigation-dropdown-item-inline-info">
          {from ? Moment(from).format('DD/MM/YYYY') : '...'} - {to ? Moment(to).format('DD/MM/YYYY') : '...'}
        </span>
      );
    }
  };

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
            className="dpw-navigation-dropdown-panel dpw-navigation-date-picker-panel dpw-navigation-dropdown-panel-corner-left">
            <div className="dpw-date-picker">

              <div className="dpw-date-picker-panel-container">
                <form>
                  <DateTimePicker
                    label="From"
                    className="dpw-date-picker-left"
                    value={from}
                    onChange={value => dispatch(setParamsAction({[fromParam]: value, delayReload: true}))}/>
                  <DateTimePicker
                    label="To"
                    className="dpw-date-picker-right"
                    value={to}
                    onChange={value => dispatch(setParamsAction({[toParam]: value, delayReload: true}))}/>
                </form>
              </div>
            </div>
          </div>
        </Menu>
      </FilterItem>
    );
  }
}