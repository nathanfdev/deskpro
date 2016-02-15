import { dispatchInAgent, fakeRecordsStore } from 'Helpers';

describe('RecordsStore actions', () => {

  const loadBatch = require('DeskPRO/Bundle/AppBundle/Modules/RecordsStore').loadBatch;
  const DAL       = require('DeskPRO/Bundle/AppBundle/DAL/DAL');

  describe('loadBatch() action', () => {
    it('should create Flux Standard Action', () => {
      const action = loadBatch();
      expect(action.type).toBeDefined();
      expect(action.payload).toBeDefined();
    });

    it('should request records via record repository', () => {
      spyOn(DAL, 'repository').andCallThrough();
      dispatchInAgent(loadBatch('Ticket', [1, 2, 3], 'test'));
      expect(DAL.repository).toHaveBeenCalledWith('Ticket');
    });

    it('should call loadBatch() on the record\'s repository', () => {
      const TicketRepository = jasmine.createSpyObj('TicketRepository', ['loadBatch']);
      spyOn(DAL, 'repository').andReturn(TicketRepository);
      TicketRepository.loadBatch.andReturn({then: () => {}});
      dispatchInAgent(loadBatch('Ticket', [1, 2, 3], 'test'));
      expect(TicketRepository.loadBatch).toHaveBeenCalledWith([1, 2, 3]);
    });

    it('should pass only missing record IDs to the repository\'s loadBatch()', () => {
      const fakeState = fakeRecordsStore({Ticket: {records: {2: {id: 2}}}});
      const TicketRepository = jasmine.createSpyObj('TicketRepository', ['loadBatch']);
      spyOn(DAL, 'repository').andReturn(TicketRepository);
      TicketRepository.loadBatch.andReturn({then: () => {}});
      dispatchInAgent(loadBatch('Ticket', [1, 2, 3], 'test'), fakeState);
      expect(TicketRepository.loadBatch).toHaveBeenCalledWith([1, 3]);
    });
  })
});
