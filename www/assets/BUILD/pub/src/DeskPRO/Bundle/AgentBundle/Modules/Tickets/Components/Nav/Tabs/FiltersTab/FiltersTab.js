import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { NestedList } from './NestedList';

export class FiltersTab extends Component {
  static propTypes = {
    filterSetsCount: PropTypes.object.isRequired
  };

  getFilterSetCounts(count) {
    if (count) {
      const nested = count.get('nested');
      if (nested) {
        return nested.toJS();
      }
    }

    return [];
  }

  render() {
    return (
      <div>
        {this.props.filterSetsCount.entrySeq().map(([id, count]) => (
          <div key={count.get('id')}>
            <SectionHeader>{count.get('title')}</SectionHeader>

            <NestedList items={this.getFilterSetCounts(count)} alwaysExpanded />
          </div>
        ))}
      </div>
    );
  }
}
