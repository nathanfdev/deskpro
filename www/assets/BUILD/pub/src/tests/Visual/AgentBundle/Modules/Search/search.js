import React from 'react';
import { storiesOf } from '@storybook/react';
import SearchResults from 'DeskPRO/Bundle/AgentBundle/Modules/Search/Components/SearchResults';
import { css } from '../../../decorators';
import results from '../../../../DemoState/AgentBundle/Modules/Search/result.json';
import resultsTicketsOnly from '../../../../DemoState/AgentBundle/Modules/Search/result_tickets_only.json';
import partialResult from '../../../../DemoState/AgentBundle/Modules/Search/partial_result.json';

storiesOf('Agent: Search', module)
  .addDecorator(story => css(story()))
  .add(
    'Results',
    () => <SearchResults results={results} />
  )
  .add(
    'Partial result',
    () => <SearchResults results={partialResult} />
  )
  .add(
    'Tickets only',
    () => <SearchResults results={resultsTicketsOnly} />
  )
;
