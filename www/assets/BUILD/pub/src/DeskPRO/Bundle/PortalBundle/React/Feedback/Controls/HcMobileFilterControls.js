import PropTypes from 'prop-types';
import React from 'react';
import { HcMobileTabRow } from './HcMobileTabRow';
import { HcMobileFilters } from './HcMobileFilters';

export class HcMobileFilterControls extends React.Component {

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

  updateFilter(filter) {
    this.props.updateFilter(filter);
  }

  render() {
    const { available, filterModel } = this.props;

    return (
      <div className="d-block d-sm-none">
        <div className="dp-po-community-header-mobile">
          <HcMobileTabRow
            available={available}
            filter={filterModel}
            setView={this.onSetView}
          />
          <HcMobileFilters
            filter={filterModel}
            onSetStatus={this.onSetStatus}
            onSetStatusCategory={this.onSetStatusCategory}
            onSetActivity={this.onSetActivity}
            onResetActivities={this.onResetActivities}
            setSort={this.onSetSort}
          />
        </div>
      </div>
    );
  }
}
