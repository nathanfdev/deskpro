jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatCard');

describe('ChatCard', () => {

  const React = require('react/addons');
  const TestUtils = React.addons.TestUtils;
  const ChatCard = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatCard').ChatCard;

  it('should render a chat card', () => {
    const component = TestUtils.renderIntoDocument(<ChatCard chat={{}} />);
    const cards = TestUtils.scryRenderedDOMComponentsWithClass(component, 'chat-card');
    expect(cards.length).toEqual(1);
  });

});
