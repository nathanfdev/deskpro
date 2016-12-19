import React from 'react';
import { Tab } from './Tab';
import { SortWidget } from './SortWidget';
import _ from 'lodash';

export class TabRow extends React.Component {

  render() {
    return (
      <ul className="flat-tabs">
        {_.map(this.props.available.status, (status, status_id) =>
          <Tab
            key={status_id}
            label={status}
            id={status_id}
            active={this.props.filter.getStatus() == status_id}
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
