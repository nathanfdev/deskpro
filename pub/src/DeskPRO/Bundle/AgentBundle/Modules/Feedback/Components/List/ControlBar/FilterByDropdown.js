import React, {PropTypes} from 'react';
import Formsy from 'formsy-react';
import Moment from 'moment';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import {FilterItem} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';
import {DateTimePicker} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTimePicker';
import {ChoiceMenu} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';
import { setFilterValue, loadFeedbackList } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { TypesCollectionContainer } from './TypesCollectionContainer';
import { StatusesCollectionContainer } from './StatusesCollectionContainer';

const FilterByDropdown = React.createClass({

  propTypes: {
    statuses: PropTypes.object.isRequired,
    types: PropTypes.object.isRequired,
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

  getInitialFromTo(active) {
    let from = null;
    let to = null;
    if (active) {
      const {filterParams} = this.props;
      if (filterParams.get('date_created')) {
        const dateCreated = filterParams.get('date_created');
        from = dateCreated.get('created_from');
        to = dateCreated.get('created_to');
      }
    }
    return { from: from, to: to };
  },

  submitFilter(type, model) {
    const {dispatch} = this.props;
    dispatch(setFilterValue({ filter: type, value: model }));
    dispatch(loadFeedbackList());
    // this.props.toggleDropdown();
  },

  resetFilter(type) {
    console.log('Reset: ', type);
    const {dispatch} = this.props;
    dispatch(setFilterValue({ filter: type, value: null }));
    dispatch(loadFeedbackList());
  },

  checkIfActiveDateCreatedFilter() {
    const {filterParams} = this.props;
    if (filterParams && filterParams.get('date_created')) {
      return true;
    }
  },

  checkIfActiveTypesFilter() {
    const {filterParams} = this.props;
    if (filterParams && filterParams.get('category')) {
      return true;
    }
  },

  checkIfActiveStatusesFilter() {
    const {filterParams} = this.props;
    if (filterParams && (filterParams.get('status') || filterParams.get('status_category'))) {
      return true;
    }
  },

  renderDateCreatedItemContent(active, initialFromTo) {
    if (active) {
      const from = initialFromTo.from ? Moment(initialFromTo.from).format('DD/MM/YYYY') : '...';
      const to = initialFromTo.to ? Moment(initialFromTo.to).format('DD/MM/YYYY') : '...';
      return (
        <span className="dpw-navigation-dropdown-item-inline-info">{from} - {to}</span>
      );
    }
  },

  renderTypeItemContent(active) {
    if (active) {
      const {filterParams} = this.props;
      const chosenValues = filterParams.get('category').toJS();
      return (
        <span className="dpw-navigation-dropdown-item-inline-info">{chosenValues[0]}</span>
      );
    }
  },

  renderTypeItemExtraContent(active) {
    if (active) {
      const {filterParams} = this.props;
      const chosenValues = filterParams.get('category').toJS();
      let size = chosenValues.length;
      if (size > 1) {
        return (
          <span className="dpw-navigation-dropdown-item-inline-info dpw-navigation-dropdown-item-inline-info-extra">
            +{size - 1}
          </span>
        );
      }
    }
  },

  render() {
    const {filterParams} = this.props;
    const isDateCreatedFilterActive = this.checkIfActiveDateCreatedFilter();
    const initialFromTo = this.getInitialFromTo(isDateCreatedFilterActive);
    const isTypesFilterActive = this.checkIfActiveTypesFilter();
    const isStatusesFilterActive = this.checkIfActiveStatusesFilter();
    return (
      <Menu>
        <FilterItem
          filterType="category"
          isActive={isTypesFilterActive}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Type"
          >
          {this.renderTypeItemContent(isTypesFilterActive)}
          {this.renderTypeItemExtraContent(isTypesFilterActive)}
          <Menu>
            <TypesCollectionContainer />
          </Menu>
        </FilterItem>
        <FilterItem
          filterType="status"
          isActive={isStatusesFilterActive}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Status">
          <Menu>
            <ChoiceMenu title="Feedback Status">
              <StatusesCollectionContainer/>
            </ChoiceMenu>
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
          isActive={isDateCreatedFilterActive}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Date"
          >
          {this.renderDateCreatedItemContent(isDateCreatedFilterActive, initialFromTo)}
          <Menu>
            <div
              className="dpw-navigation-dropdown-panel dpw-navigation-date-picker-panel dpw-navigation-dropdown-panel-corner-left">

              <span className="dpw-navigation-dropdown-panel-close"><i className="fa fa-times"></i></span>

              <div className="dpw-date-picker">

                <div className="dpw-date-picker-panel-container">
                  <Formsy.Form onValidSubmit={this.submitFilter.bind(this, 'date_created')}>
                    <DateTimePicker
                      label="From"
                      name="created_from"
                      className="dpw-date-picker-left"
                      initialValue={initialFromTo.from}
                      />
                    <DateTimePicker
                      label="To"
                      name="created_to"
                      className="dpw-date-picker-right"
                      initialValue={initialFromTo.to}
                      />

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
