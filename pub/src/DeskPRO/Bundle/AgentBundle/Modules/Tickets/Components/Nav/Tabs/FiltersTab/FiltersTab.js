import React, { Component, PropTypes } from 'react';
import { SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { NestedList } from './NestedList';

export class FiltersTab extends Component {
  static propTypes = {
    filterSets: PropTypes.object.isRequired,
    filterSetsCount: PropTypes.object.isRequired,
  };

  render() {
    return (
      <div>
        {this.props.filterSets.map(filterSet => (
          <div key={filterSet.get('id')}>
            <SectionHeader>{filterSet.get('title')}</SectionHeader>

            <NestedList
              items={this.getFilterSetCounts(filterSet)}
              alwaysExpanded
            />
          </div>
        ))}
      </div>
    );
  }

  getFilterSetCounts(filterSet) {
    const count = this.props.filterSetsCount.get(filterSet.get('id'));
    if (count) {
      const nested = count.get('nested');
      if (nested) {
        return nested.toJS();
      }
    }

    return [];
  }
}
