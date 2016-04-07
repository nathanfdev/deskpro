// #define ~Card DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/List/View/Card

jest.dontMock('~Card/CrmCardContainer');

import React from 'react';
import { renderInCrmApp } from '../../../../crm.test-helper';
import { toImmutable } from 'Helpers';

describe('CRM: CrmCardContainer', () => {
  const CrmCardContainer = require('~Card/CrmCardContainer').CrmCardContainer;
  const OrganizationCard = require('~Card/OrganizationCard').OrganizationCard;
  const PersonCard       = require('~Card/PersonCard').PersonCard;

  const fakeState = {
    CRM: {
      list: toImmutable({
        elements:          [1, 2, 3],
        currentListParams: {
          content: 'organizations'
        }
      })
    }
  };

  const render = () => {
    renderInCrmApp(fakeState, <CrmCardContainer />);
  };

  it('should render FeedbackCard', () => {
    spyOn(OrganizationCard.prototype, 'render').and.callThrough();
    spyOn(PersonCard.prototype, 'render').and.callThrough();
    render();
    expect(OrganizationCard.prototype.render).toHaveBeenCalled();
    expect(PersonCard.prototype.render).not.toHaveBeenCalled();
  });
});
