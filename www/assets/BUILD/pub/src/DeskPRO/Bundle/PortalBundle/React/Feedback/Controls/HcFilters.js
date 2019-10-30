import PropTypes from 'prop-types';
import React from 'react';
import { HcFilterFacets } from './HcFilterFacets';
import { HcViewModes } from './HcViewModes';
import { HcSortWidget } from './HcSortWidget';

export class HcFilters extends React.Component {
  static propTypes = {
    filter:              PropTypes.object,
    onSetStatus:         PropTypes.func,
    onSetStatusCategory: PropTypes.func,
    onSetActivity:       PropTypes.func,
    onResetActivities:   PropTypes.func,
    onSetViewMode:       PropTypes.func,
    setSort:             PropTypes.func,
  };

  render() {
    return (
      <div className="dp-po-community-header-right">
        <HcFilterFacets
          filter={this.props.filter}
          onSetStatus={this.props.onSetStatus}
          onSetStatusCategory={this.props.onSetStatusCategory}
          onSetActivity={this.props.onSetActivity}
          onResetActivities={this.props.onResetActivities}
        />
        <HcViewModes
          filter={this.props.filter}
          onSetViewMode={this.props.onSetViewMode}
        />
        <HcSortWidget
          filter={this.props.filter}
          setSort={this.props.setSort}
        />
      </div>
    );
  }
}
