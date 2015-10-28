import React, { Component, PropTypes } from 'react';
import { SectionHeader, NestedList } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class FiltersTab extends Component {
  static propTypes = {
    filterSets: PropTypes.object.isRequired,
    filterSetsCount: PropTypes.object.isRequired,
    filterNames: PropTypes.object.isRequired,
    onItemControlClick: PropTypes.func.isRequired
  };

  render() {
    const { filterSets, filterSetsCount, filterNames, onItemControlClick } = this.props;

    const getCounts = (filterSet) => {
      const count = filterSetsCount.get(filterSet.get('id'));
      if (count) {
        const nested = count.get('nested');
        if (nested) {
          return nested.toJS();
        }
      }

      return [];
    };

    return (
      <div>
        {filterSets.map(fs => (
          <div key={fs.get('id')}>
            <SectionHeader>{fs.get('title')}</SectionHeader>

            <NestedList
              items={getCounts(fs)}
              groups={filterNames.toJS()}
              onClick={() => alert(1)}
              onItemControlClick={onItemControlClick}
            />
          </div>
        ))}
      </div>
    );
  }
}
