jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatsList');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatCard');

describe('ChatsList', () => {

  const React = require('react/addons');
  const TestUtils = React.addons.TestUtils;
  const ChatsList = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatsList').ChatsList;
  const ChatCard = require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/View/List/ChatCard').ChatCard;

  it('should render 3 ChatCard elements when passing 3 children', () => {
    spyOn(ChatCard.prototype, 'render').andCallThrough();
    TestUtils.renderIntoDocument(<ChatsList elements={[{}, {}, {}]} />);
    expect(ChatCard.prototype.render.calls.length).toEqual(3);
  });

});
