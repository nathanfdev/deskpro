import React from 'react';
import renderer from 'react-test-renderer';
import Immutable from 'immutable';
import { IntlProvider } from 'react-intl';
import AgentFilters from 'DeskPRO/Bundle/AgentBundle/Modules/Filters/Components/AgentFilters';
import { filterSets, filters, stars } from 'DemoState/AgentBundle/Modules/Filters/filters';

describe('>>> Filters --- Snapshot', () => {
  it('+++capturing Snapshot of Filters', (props = {
    locale: 'en',
    messages: { 'agent.search.type_ticket': 'Tickets' }
  }) => {
    const renderedValue = renderer.create(
      <IntlProvider {...props}>
        <AgentFilters
          filterSets={filterSets}
          filters={filters}
          filtersCounts={Immutable.fromJS([])}
          labels={Immutable.fromJS([])}
          stars={stars}
          starsCounts={Immutable.fromJS([])}
        />
      </IntlProvider>
    ).toJSON();
    expect(renderedValue).toMatchSnapshot();
  });
});
