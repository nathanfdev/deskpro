import { dispatchInAgent, /* getReduxState, */ fakeRecordsStore } from 'Helpers';

describe('RecordsStore loadAll() action', () => {
  const loadAll = require('DeskPRO/Bundle/AppBundle/Modules/RecordsStore').loadAll;
  const DAL     = require('DeskPRO/Bundle/AppBundle/DAL/DAL');

  // it('should call loadAll() on the record\'s repository', () => {
  //   const TicketRepository = jasmine.createSpyObj('TicketRepository', ['loadAll']);
  //   spyOn(DAL, 'repository').and.returnValue(TicketRepository);
  //   TicketRepository.loadAll.and.returnValue({then: () => {}});
  //
  //   dispatchInAgent(loadAll('Ticket'));
  //
  //   expect(TicketRepository.loadAll).toHaveBeenCalled();
  // });

  // it('should return payload.promise when called for the first time', () => {
  //   const fakeState = fakeRecordsStore({Ticket: {records: {}}});
  //   const TicketRepository = jasmine.createSpyObj('TicketRepository', ['loadAll']);
  //   spyOn(DAL, 'repository').and.returnValue(TicketRepository);
  //   TicketRepository.loadAll.and.returnValue({then: () => {}});
  //
  //   const action = dispatchInAgent(loadAll('Ticket'), fakeState);
  //
  //   expect(Object.keys(action.payload)).toContain('promise');
  // });

  it('should return true in payload.noUpdates when already loaded', () => {
    const fakeState = fakeRecordsStore({ Ticket: { records: { 1: {} }, collections: { all: [1] }, statuses: { all: { success: true } } } });
    const action = dispatchInAgent(loadAll('Ticket'), fakeState);
    expect(action.payload.noUpdates).toBeTruthy();
  });

  it('should return true in payload.noUpdates when called while the previous call is loading', () => {
    const fakeState = fakeRecordsStore({ Ticket: { records: { 1: {} }, collections: {}, statuses: { all: { loading: true } } } });
    const action = dispatchInAgent(loadAll('Ticket'), fakeState);
    expect(action.payload.noUpdates).toBeTruthy();
  });

  it('should not call repository if collection is loading', () => {
    const fakeState = fakeRecordsStore({ Ticket: { records: { 1: {} }, collections: { all: [1] }, statuses: { all: { loading: true } } } });
    spyOn(DAL, 'repository');

    dispatchInAgent(loadAll('Ticket'), fakeState);

    expect(DAL.repository).not.toHaveBeenCalled();
  });

  it('should not call repository if collection is already loaded', () => {
    const fakeState = fakeRecordsStore({ Ticket: { records: { 1: {} }, collections: { all: [1] }, statuses: { all: { success: true } } } });
    spyOn(DAL, 'repository');

    dispatchInAgent(loadAll('Ticket'), fakeState);

    expect(DAL.repository).not.toHaveBeenCalled();
  });
});
