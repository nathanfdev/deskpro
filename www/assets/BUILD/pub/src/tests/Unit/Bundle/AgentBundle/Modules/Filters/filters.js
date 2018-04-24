import React from 'react';
import renderer from 'react-test-renderer';
import { IntlProvider } from 'react-intl';
import AgentFilters from 'DeskPRO/Bundle/AgentBundle/Modules/Filters/Components/AgentFilters';
import { filterSets, filters, stars } from 'DemoState/AgentBundle/Modules/Filters/filters';

describe('>>> Filters --- Snapshot', () => {
  it('+++capturing Snapshot of Filters', (props = { locale: 'en' }) => {
    const renderedValue = renderer.create(
      <IntlProvider {...props}>
        <AgentFilters
          filterSets={filterSets}
          filters={filters}
          filtersCounts={[]}
          labels={[]}
          stars={stars}
          starsCounts={[]}
        />
      </IntlProvider>
    ).toJSON();
    expect(renderedValue).toMatchSnapshot();
  });
});
