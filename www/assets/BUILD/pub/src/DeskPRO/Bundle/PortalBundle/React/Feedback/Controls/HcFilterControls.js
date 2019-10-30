import PropTypes from 'prop-types';
import React from 'react';
import { HcTabRow } from './HcTabRow';
import { HcSearch } from './HcSearch';
import { HcFilters } from './HcFilters';

export class HcFilterControls extends React.Component {

  static propTypes = {
    available:    PropTypes.object,
    updateFilter: PropTypes.func,
    filterModel:  PropTypes.object,
  };

  onSetStatusCategory = (statusCategoryId) => {
    const { filterModel } = this.props;
    filterModel.toggleStatusCategory(statusCategoryId);

    this.updateFilter(filterModel);
  };

  onSetStatus = (statusId) => {
    const { filterModel } = this.props;
    filterModel.status_categories = [];
    filterModel.setStatus(statusId);

    this.updateFilter(filterModel);
  };

  onSetSort = (newSort) => {
    const { filterModel } = this.props;
    filterModel.changeSort(newSort);

    this.updateFilter(filterModel);
  };

  onSetView = (viewId) => {
    const { filterModel } = this.props;
    filterModel.setView(viewId);

    this.updateFilter(filterModel);
  };

  onSearch = (q) => {
    const { filterModel } = this.props;
    filterModel.setQ(q);

    this.updateFilter(filterModel);
  };

  onSetActivity = (activityId) => {
    const { filterModel } = this.props;
    filterModel.toggleActivity(activityId);

    this.updateFilter(filterModel);
  };

  onResetActivities = () => {
    const { filterModel } = this.props;
    filterModel.resetActivities();

    this.updateFilter(filterModel);
  };

  onSetViewMode = (mode) => {
    const { filterModel } = this.props;
    filterModel.setViewMode(mode);

    this.updateFilter(filterModel);
  };

  updateFilter(filter) {
    this.props.updateFilter(filter);
  }

  render() {
    const { available, filterModel } = this.props;

    return (
      <div className="dp-po-community-header">
        <HcTabRow
          available={available}
          filter={filterModel}
          setView={this.onSetView}
        />
        <HcSearch
          filter={filterModel}
          setSearch={this.onSearch}
        />
        <HcFilters
          filter={filterModel}
          onSetStatus={this.onSetStatus}
          onSetStatusCategory={this.onSetStatusCategory}
          onSetActivity={this.onSetActivity}
          onResetActivities={this.onResetActivities}
          onSetViewMode={this.onSetViewMode}
          setSort={this.onSetSort}
        />
      </div>
    );
  }
}
