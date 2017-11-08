import PropTypes from 'prop-types';
import React from 'react';
import map from 'lodash/map';
import { Tab } from './Tab';
import { SortWidget } from './SortWidget';

export class TabRow extends React.Component {
  static propTypes = {
    available:         PropTypes.object,
    filter:            PropTypes.object,
    setSort:           PropTypes.func,
    setStatus:         PropTypes.func,
    setStatusCategory: PropTypes.func,
  };

  render() {
    return (
      <ul className="flat-tabs">
        {map(this.props.available.status, (status, statusId) =>
          <Tab
            key={statusId}
            label={status}
            id={statusId}
            active={this.props.filter.getStatus() === statusId}
            activeCategories={this.props.filter.status_categories}
            setStatus={this.props.setStatus}
            available={this.props.available}
            setStatusCategory={this.props.setStatusCategory}
            types={this.props.filter.getSelectedTypes()}
          />
        )}
        <SortWidget filter={this.props.filter} setSort={this.props.setSort} />
      </ul>
    );
  }
}
