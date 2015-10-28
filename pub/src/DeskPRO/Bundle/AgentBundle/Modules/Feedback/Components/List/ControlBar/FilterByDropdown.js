import React, {PropTypes} from 'react';
import Formsy from 'formsy-react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import {FilterItem} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';
import {DateTimePicker} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTimePicker';

const FilterByDropdown = React.createClass({

  propTypes: {
    filterOptions: PropTypes.array.isRequired,
    currentFilterMode: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  },
  /*

   mixins: [require('react-onclickoutside')],
   */

  getInitialState() {
    return {
      category: { value: false },
      status: { value: { some: true } },
      custom_category: { value: false },
      date_created: { from: false, to: false }
    };
  },

  /* handleClickOutside(event) {
   console.log('Click outside', event);
   this.props.toggleDropdown();
   },*/

  resetFilter(type) {
    this.setState({
      [type]: { value: false }
    });
  },


  submit(model) {
    console.log(model);
    this.setState({ date_created: model });
    console.log('State', this.state.date_created);
    //this.props.toggleDropdown();
  },

  render() {
    return (
      <Menu>
        <FilterItem
          filterType="category"
          isActive={Boolean(this.state.category.value)}
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
          isActive={Boolean(this.state.status.value)}
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
          isActive={Boolean(this.state.custom_category.value)}
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
          isActive={Boolean(this.state.date_created.value)}
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
                  <Formsy.Form onValidSubmit={this.submit}>
                    <DateTimePicker name="from" className="dpw-date-picker-left"/>
                    <DateTimePicker name="to" className="dpw-date-picker-right"/>

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
