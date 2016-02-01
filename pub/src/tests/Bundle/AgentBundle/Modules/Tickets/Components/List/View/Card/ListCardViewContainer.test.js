// #define ~ListView DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/List/View/Card

jest.dontMock('~ListView/ListCardViewContainer');

import React from 'react';
import { fakeRecordStoreState, toImmutable } from 'Helpers/redux';
import { renderInTicketsApp } from '../../../../tickets.test-helper';

const fakeState = {
  Tickets: {list: toImmutable({elements: [1, 2, 3]})},
  RecordStores: {Tickets: {tickets: fakeRecordStoreState({1: {}, 2: {}, 3: {}}, {tickets: [1, 2, 3]})}}
};

describe('Tickets List: ListCardViewContainer', () => {
  const ListCardViewContainer = require('~ListView/ListCardViewContainer').ListCardViewContainer;
  const TicketCard = require('~ListView/TicketCard').TicketCard;

  it('should render 3 TicketCard elements when passing 3 children', () => {
    spyOn(TicketCard.prototype, 'render').andCallThrough();
    renderInTicketsApp(fakeState, <ListCardViewContainer />);
    expect(TicketCard.prototype.render.calls.length).toEqual(3);
  });
});
