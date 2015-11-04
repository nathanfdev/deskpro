import React, {PropTypes} from 'react';
import Formsy from 'formsy-react';
import Moment from 'moment';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import {FilterItem} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';
import {DateTimePicker} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTimePicker';
import { setFilterValue, loadFeedbackList } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { loadCommentsList } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';
import { TypesCollectionContainer } from './TypesCollectionContainer';
import { CategoriesCollectionContainer } from './CategoriesCollectionContainer';
import { StatusesCollectionContainer } from './StatusesCollectionContainer';

const FilterByDropdown = React.createClass({

  propTypes: {
    navItem: PropTypes.object,
    statuses: PropTypes.object.isRequired,
    types: PropTypes.object.isRequired,
    filterParams: PropTypes.object.isRequired,
    isComments: PropTypes.bool.isRequired,
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
    const {dispatch, isComments} = this.props;
    dispatch(setFilterValue({ filter: type, value: model }));
    if (isComments) {
      dispatch(loadCommentsList());
    } else {
      dispatch(loadFeedbackList());
    }
    // this.props.toggleDropdown();
  },

  resetFilter(type) {
    const {dispatch, isComments} = this.props;
    dispatch(setFilterValue({ filter: type, value: null }));
    if (isComments) {
      dispatch(loadCommentsList());
    } else {
      dispatch(loadFeedbackList());
    }
  },

  checkIfDateCreatedFilterIsActive() {
    const {filterParams} = this.props;
    if (filterParams && filterParams.get('date_created')) {
      return true;
    }
  },

  checkIfTypesFilterIsActive() {
    const {filterParams} = this.props;
    if (filterParams && filterParams.get('category') && filterParams.get('category').size > 0) {
      return true;
    }
  },

  checkIfCategoriesFilterIsActive() {
    const {filterParams} = this.props;
    if (filterParams && filterParams.get('custom_category') && filterParams.get('custom_category').size > 0) {
      return true;
    }
  },

  checkIfStatusesFilterIsActive() {
    const {filterParams} = this.props;
    if (filterParams) {
      if (filterParams.get('status') && filterParams.get('status').size > 0) {
        return true;
      }
      if (filterParams.get('status_category') && filterParams.get('status_category').size > 0) {
        return true;
      }
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
      const size = chosenValues.length;
      if (size > 1) {
        return (
          <span className="dpw-navigation-dropdown-item-inline-info dpw-navigation-dropdown-item-inline-info-extra">
            +{size - 1}
          </span>
        );
      }
    }
  },

  renderCategoryItemContent(active) {
    if (active) {
      const {filterParams} = this.props;
      const chosenValues = filterParams.get('custom_category').toJS();
      return (
        <span className="dpw-navigation-dropdown-item-inline-info">{chosenValues[0]}</span>
      );
    }
  },

  renderCategoryItemExtraContent(active) {
    if (active) {
      const {filterParams} = this.props;
      const chosenValues = filterParams.get('custom_category').toJS();
      const size = chosenValues.length;
      if (size > 1) {
        return (
          <span className="dpw-navigation-dropdown-item-inline-info dpw-navigation-dropdown-item-inline-info-extra">
            +{size - 1}
          </span>
        );
      }
    }
  },

  renderStatusItemContent(active) {
    if (active) {
      let chosenValues = [];
      const {filterParams} = this.props;
      if (filterParams.get('status') && filterParams.get('status').size > 0) {
        chosenValues = filterParams.get('status').toJS();
      } else if (filterParams.get('status_category') && filterParams.get('status_category').size > 0) {
        chosenValues = filterParams.get('status_category').toJS();
      }
      if (chosenValues.length > 0) {
        return (
          <span className="dpw-navigation-dropdown-item-inline-info">{chosenValues[0]}</span>
        );
      }
    }
  },

  renderStatusItemExtraContent(active) {
    if (active) {
      let chosenValues = [];
      const {filterParams} = this.props;
      if (filterParams.get('status')) {
        chosenValues = filterParams.get('status').toJS();
      }
      if (filterParams.get('status_category')) {
        chosenValues = chosenValues.concat(filterParams.get('status_category').toJS());
      }
      const size = chosenValues.length;
      if (size > 1) {
        return (
          <span className="dpw-navigation-dropdown-item-inline-info dpw-navigation-dropdown-item-inline-info-extra">
            +{size - 1}
          </span>
        );
      }
    }
  },

  renderCategoryFilterItem(navItem) {
    if (!navItem || !navItem.toJS().hasOwnProperty('custom_category')) {
      const isCategoriesFilterActive = this.checkIfCategoriesFilterIsActive();

      return (
        <FilterItem
          filterType="custom_category"
          isActive={isCategoriesFilterActive}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Category">
          {this.renderCategoryItemContent(isCategoriesFilterActive)}
          {this.renderCategoryItemExtraContent(isCategoriesFilterActive)}
          <Menu>
            <CategoriesCollectionContainer />
          </Menu>
        </FilterItem>
      );
    }
    return (<div/>);
  },

  renderTypeFilterItem(navItem) {
    if (!navItem || !navItem.toJS().hasOwnProperty('category')) {
      const isTypesFilterActive = this.checkIfTypesFilterIsActive();

      return (
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
      );
    }
    return (<div/>);
  },

  renderStatusFilterItem(navItem) {
    if (!navItem || (!navItem.toJS().hasOwnProperty('status') && !navItem.toJS().hasOwnProperty('status_category'))) {
      const isStatusesFilterActive = this.checkIfStatusesFilterIsActive();

      return (
        <FilterItem
          filterType="status"
          isActive={isStatusesFilterActive}
          resetFilter={this.resetFilter}
          icon="calendar-o"
          label="Status">
          {this.renderStatusItemContent(isStatusesFilterActive)}
          {this.renderStatusItemExtraContent(isStatusesFilterActive)}
          <Menu>
            <StatusesCollectionContainer/>
          </Menu>
        </FilterItem>
      );
    }
    return (<div/>);
  },

  render() {
    const {navItem} = this.props;
    const isDateCreatedFilterActive = this.checkIfDateCreatedFilterIsActive();
    const initialFromTo = this.getInitialFromTo(isDateCreatedFilterActive);
    return (
      <Menu>
        {this.renderTypeFilterItem(navItem)}
        {this.renderStatusFilterItem(navItem)}
        {this.renderCategoryFilterItem(navItem)}
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
