import React from 'react';
import renderer from 'react-test-renderer';
import AgentFilters from 'DeskPRO/Bundle/AgentBundle/Modules/Filters/Components/Filters';
import { filterSets, filters, stars } from 'DemoState/AgentBundle/Modules/Filters/filters';

describe('>>> Filters --- Snapshot', () => {
  it('+++capturing Snapshot of Filters', () => {
    const renderedValue = renderer.create(
      <AgentFilters
        filterSets={filterSets}
        filters={filters}
        stars={stars}
      />
    ).toJSON();
    expect(renderedValue).toMatchSnapshot();
  });
});