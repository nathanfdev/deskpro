jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs/FiltersTab/UrgencyList');

import React from 'react';
import TestUtilAdditions from 'react-testutils-additions';
import { renderToStaticMarkup } from 'react-dom/server';
import { toImmutable } from 'helpers';

describe('Tickets Navigation: UrgencyList component', () => {
  const UrgencyList =
          require('DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs/FiltersTab/UrgencyList').UrgencyList;

  const items = toImmutable([
    { id: 1, count: 1 }, { id: 3, count: 42 }
  ]);

  it('should render urgency  sliders', () => {
    const component = TestUtilAdditions.renderIntoDocument(<UrgencyList items={items} />);

    expect(TestUtilAdditions.find(component, '.slider.level-1').length).toEqual(1);
    expect(TestUtilAdditions.find(component, '.slider.level-3').length).toEqual(1);
    expect(TestUtilAdditions.find(component, '.slider').length).toEqual(2);
  });
});
