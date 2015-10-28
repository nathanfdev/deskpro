import React, {PropTypes} from 'react';
import Formsy from 'formsy-react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import {FilterItem} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';
import {DateTimePicker} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTimePicker';
import { setFilterValue } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

const FilterByDropdown = React.createClass({

  propTypes: {
    filterParams: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  },
  /*

   mixins: [require('react-onclickoutside')],

   handleClickOutside(event) {
   console.log('Click outside', event);
   this.props.toggleDropdown();
   },*/

  resetFilter(type) {
    const {dispatch} = this.props;
    dispatch(setFilterValue({ type: type, value: null }));
  },

  submitDateCreatedFilter(model) {
    const {dispatch} = this.props;
    console.log(model);
    dispatch(setFilterValue(model));
    // this.props.toggleDropdown();
  },

  checkIfActiveDateCreated() {
    const {filterParams} = this.props;
    if (filterParams && (filterParams.get('created_from') || filterParams.get('created_to'))) {
      return true;
    }
  },

  render() {
    const {filterParams} = this.props;
    console.log('Params', filterParams);
    const initialFrom = filterParams && filterParams.get('created_from') ? filterParams.get('created_from') : null;
    const initialTo = filterParams && filterParams.get('created_to') ? filterParams.get('created_to') : null;
    return (
      <Menu>
        <FilterItem
          filterType="category"
          isActive={Boolean(filterParams && filterParams.category)}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Type"
          >
          <Menu>
            <Item label="Sub-menu 2"/>
            <Item label="Subterranean"/>
          </Menu>
        </FilterItem>
        <FilterItem
          filterType="status"
          isActive={Boolean(filterParams && filterParams.status)}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Status">
          <Menu>
            <Item label="Sub-menu 2"/>
            <Item label="Subterranean"/>
          </Menu>
        </FilterItem>
        <FilterItem
          filterType="custom_category"
          isActive={Boolean(filterParams && filterParams.custom_category)}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Category">
          <Menu>
            <Item label="Sub-menu 2"/>
            <Item label="Subterranean"/>
          </Menu>
        </FilterItem>
        <FilterItem
          filterType="date_created"
          isActive={this.checkIfActiveDateCreated()}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Date"
          >
          <Menu>
            <div
              className="dpw-navigation-dropdown-panel dpw-navigation-date-picker-panel dpw-navigation-dropdown-panel-corner-left">

              <span className="dpw-navigation-dropdown-panel-close"><i className="fa fa-times"></i></span>

              <div className="dpw-date-picker">

                <div className="dpw-date-picker-panel-container">
                  <Formsy.Form onValidSubmit={this.submitDateCreatedFilter}>
                    <DateTimePicker name="created_from" className="dpw-date-picker-left"
                                    initialValue={initialFrom}/>
                    <DateTimePicker name="created_to" className="dpw-date-picker-right"
                                    initialValue={initialTo}/>

                    <div className=" dpw-date-picker-footer">
                      <button type="submit" className="dpw--panel-button">Apply Date Range Filter</button>
                    </div>
                  </Formsy.Form>
                </div>


              </div>
            </div>
          </Menu>
        </FilterItem>
      </Menu>
    );
  }
});

module.exports.FilterByDropdown = FilterByDropdown;
