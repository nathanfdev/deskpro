import React, { Component, PropTypes } from 'react';
import { SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { NestedListContainer } from './NestedListContainer';

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
        {this.props.filterSetsCount.map(count => (
          <div key={count.get('id')}>
            <SectionHeader>{count.get('title')}</SectionHeader>

            <NestedListContainer
              items={this.getFilterSetCounts(count)}
              alwaysExpanded
              />
          </div>
        ))}
      </div>
    );
  }
}
