jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/List/View/Card/TicketCard');
jest.mock('react-intl');

import React from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import { renderInRedux, fakeState, toImmutable } from 'Helpers';

describe('Tickets List: TicketCard', () => {
  const TicketCard = require('DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/List/View/Card/TicketCard').TicketCard;

  it('should render ticket title', () => {
    const props = {
      ticket: toImmutable({subject: 'Test ticket', status: 'test'}),
      fields: toImmutable([]),
      toggleSelected: () => null
    };
    const html = renderToStaticMarkup(<TicketCard {...props} />);
    expect(html).toContain('Test ticket');
  });

  it('should render ticket labels when configured to display labels', () => {
    const props = {
      ticket: toImmutable({subject: 'Test ticket', status: 'test', labels: ['Test ticket label']}),
      fields: toImmutable(['labels']),
      toggleSelected: () => null
    };
    const html = renderToStaticMarkup(<TicketCard {...props} />);
    expect(html).toContain('Test ticket label');
  });

  it('should not render ticket labels when not configured to display them', () => {
    const props = {
      ticket: toImmutable({subject: 'Test ticket', status: 'test', labels: ['Test ticket label']}),
      fields: toImmutable([]),
      toggleSelected: () => null
    };
    const html = renderToStaticMarkup(<TicketCard {...props} />);
    expect(html).not.toContain('Test ticket label');
  });
});
