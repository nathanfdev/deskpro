// #define ~Components DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List
// #define ~Selectors DeskPRO/Bundle/AgentBundle/Modules/Chat/Selectors

jest.dontMock('~Components/ListContainer');

import { renderInRedux } from 'Helpers/redux';

describe('ListContainer', () => {
  const ListContainer = require('~Components/ListContainer').ListContainer;
  const List = require('~Components/List').List;
  const Selectors = require('~Selectors/list');

  it('should select viewMode and elements from Chat.list state', () => {
    spyOn(Selectors, 'viewModeSelector');
    spyOn(Selectors, 'elementsSelector');
    renderInRedux({}, ListContainer);
    expect(Selectors.viewModeSelector).toHaveBeenCalled();
    expect(Selectors.elementsSelector).toHaveBeenCalled();
  });

  it('should render List component', () => {
    spyOn(List.prototype, 'render').andCallThrough();
    renderInRedux({}, ListContainer);
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
