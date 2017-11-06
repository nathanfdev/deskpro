import { dispatchInAgent, fakeRecordsStore } from 'Helpers';

describe('RecordsStore loadBatch() action', () => {
  const loadBatch = require('DeskPRO/Bundle/AppBundle/Modules/RecordsStore').loadBatch; // eslint-disable-line global-require
  const DAL = require('DeskPRO/Bundle/AppBundle/DAL/DAL');                              // eslint-disable-line global-require
  const api = require('DeskPRO/Bundle/AppBundle/DAL').api;                              // eslint-disable-line global-require

  DAL.setApi(api);

  it('should create Flux Standard Action', () => {
    const action = loadBatch();
    expect(action.type).toBeDefined();
    expect(action.payload).toBeDefined();
  });

  it('should call loadBatch() on the record\'s repository', () => {
    const TicketRepository = jasmine.createSpyObj('TicketRepository', ['loadBatch']);
    spyOn(DAL, 'repository').and.returnValue(TicketRepository);
    TicketRepository.loadBatch.and.returnValue({ then: () => {} });

    dispatchInAgent(loadBatch('Ticket', [1, 2, 3], 'test'));

    expect(TicketRepository.loadBatch).toHaveBeenCalledWith([1, 2, 3]);
  });

  it('should pass only missing record IDs to the repository\'s loadBatch()', () => {
    const fakeState = fakeRecordsStore({ Ticket: { records: { 2: { id: 2 } } } });
    const TicketRepository = jasmine.createSpyObj('TicketRepository', ['loadBatch']);
    spyOn(DAL, 'repository').and.returnValue(TicketRepository);
    TicketRepository.loadBatch.and.returnValue({ then: () => {} });

    dispatchInAgent(loadBatch('Ticket', [1, 2, 3], 'test'), fakeState);

    expect(TicketRepository.loadBatch).toHaveBeenCalledWith([1, 3]);
  });

  it('should return all targets in payload.allCollectionIds', () => {
    const fakeState = fakeRecordsStore({ Ticket: { records: { 2: { id: 2 } } } });
    const TicketRepository = jasmine.createSpyObj('TicketRepository', ['loadBatch']);
    spyOn(DAL, 'repository').and.returnValue(TicketRepository);
    TicketRepository.loadBatch.and.returnValue({ then: () => {} });

    const action = dispatchInAgent(loadBatch('Ticket', [1, 2, 3], 'test'), fakeState);
    expect(action.payload.allCollectionIds).toEqual([1, 2, 3]);
  });

  it('should return empty payload.records if all requested records are loaded', () => {
    const fakeState = fakeRecordsStore({ Ticket: { records: { 1: {}, 2: {}, 3: {} } } });
    const action = dispatchInAgent(loadBatch('Ticket', [1, 2, 3], 'test'), fakeState);
    expect(action.payload.records).toEqual([]);
  });

  it('should return payload.promise if not all requested records are loaded', () => {
    const fakeState = fakeRecordsStore({ Ticket: { records: { 1: {} } } });
    const TicketRepository = jasmine.createSpyObj('TicketRepository', ['loadBatch']);
    spyOn(DAL, 'repository').and.returnValue(TicketRepository);
    TicketRepository.loadBatch.and.returnValue({ then: () => {} });

    const action = dispatchInAgent(loadBatch('Ticket', [1, 2, 3], 'test'), fakeState);

    expect(Object.keys(action.payload)).toContain('promise');
  });

  it('should not call repository if all records are already loaded', () => {
    const fakeState = fakeRecordsStore({ Ticket: { records: { 1: {}, 2: {}, 3: {} } } });
    spyOn(DAL, 'repository');

    dispatchInAgent(loadBatch('Ticket', [1, 2, 3], 'test'), fakeState);

    expect(DAL.repository).not.toHaveBeenCalled();
  });
});
