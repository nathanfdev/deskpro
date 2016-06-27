// #define ~ListView DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/List/View/Card

jest.dontMock('~ListView/ListCardViewContainer');

import React from 'react';
import { fakeRecordsStoreState, toImmutable } from 'Helpers';
import { renderInTicketsApp } from '../../../../tickets.test-helper';

const fakeState = {
  RecordsStore: {
    store: toImmutable({
      Ticket: {
        records:     { 1: {}, 2: {}, 3: {} },
        collections: { list: ['1', '2', '3'] },
        statuses:    { list: { isDone: true } }
      }
    })
  }
};

describe('Tickets List: ListCardViewContainer', () => {
  const ListCardViewContainer = require('~ListView/ListCardViewContainer').ListCardViewContainer;
  const TicketCard            = require('~ListView/TicketCard').TicketCard;

  it('should render list of the TicketCard', () => {
    spyOn(TicketCard.prototype, 'render').and.callThrough();
    renderInTicketsApp(fakeState, <ListCardViewContainer />);
    expect(TicketCard.prototype.render).toHaveBeenCalled();
  });
});
