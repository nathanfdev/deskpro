import React from 'react';
import { storiesOf } from '@storybook/react';
import { addLocaleData } from 'react-intl';
import enLocaleData from 'react-intl/locale-data/en';
import { setIntlConfig, withIntl } from 'storybook-addon-intl';
import { withKnobs, selectV2 } from '@storybook/addon-knobs';
import SearchResults from 'DeskPRO/Bundle/AgentBundle/Modules/Search/Components/SearchResults';
import { css, redux } from '../../../decorators';
import results from '../../../../DemoState/AgentBundle/Modules/Search/result.json';
import resultsTicketsOnly from '../../../../DemoState/AgentBundle/Modules/Search/result_tickets_only.json';
import partialResult from '../../../../DemoState/AgentBundle/Modules/Search/partial_result.json';

import deskpro from '../../../Resources/avatar_companies/deskpro.png';

addLocaleData(enLocaleData);

const messages = {
  en: {
    'agent.general.search':         'Search',
    'agent.general.search_results': 'Search results',
    'agent.general.show_x_more':    'Show {count} more',
    'agent.general.sort_asc':       'ASC',
    'agent.general.sort_by':        'Sort by',
    'agent.general.sort_desc':      'DESC',
    'agent.general.urgency':        'Urgency',
    'agent.search.manage_tickets':  'Manage tickets',
  }
};

const scopeOptions = {
  Global:       [],
  Ticket:       ['Ticket'],
  Content:      ['Content'],
  Person:       ['Person'],
  Organization: ['Organization'],
};

results.organizations[0].img = deskpro;

const getMessages = locale => messages[locale];

setIntlConfig({
  locales:       ['en'],
  defaultLocale: 'en',
  getMessages
});

storiesOf('Agent: Search', module)
  .addDecorator(story => css(story()))
  .addDecorator(story => redux({}, story()))
  .addDecorator(withKnobs)
  .addDecorator(withIntl)
  .add(
    'Results',
    () => <SearchResults results={results} scopes={[selectV2('Scope', scopeOptions, [])]} />
  )
  .add(
    'Partial result',
    () => <SearchResults results={partialResult} scopes={[selectV2('Scope', scopeOptions, [])]} />
  )
  .add(
    'Tickets only',
    () => <SearchResults results={resultsTicketsOnly} scopes={[selectV2('Scope', scopeOptions, [])]} />
  )
;
