// #define ~Card DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/List/View/Card

jest.dontMock('~Card/CrmCardContainer');

import React from 'react';
import { renderInCrmApp } from '../../../../crm.test-helper';
import { toImmutable } from 'Helpers';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const fakeRecords = [{ id: 1 }, { id: 2 }, { id: 3 }];

export function fakeState(content) {
  return {
    RecordsStore: {
      store: toImmutable(
        {
          Person: {
            collections: { crm: [1, 2, 3] },
            records:     mapKeyedFromArray(fakeRecords, 'id'),
            statuses:    { loading: false, success: true }
          },

          Organization: {
            collections: { crm: [1, 2, 3] },
            records:     mapKeyedFromArray(fakeRecords, 'id'),
            statuses:    { loading: false, success: true }
          }
        }
      )
    },

    CRM: {
      list: toImmutable(
        {
          elements:          [1, 2, 3],
          currentListParams: { content },

          fields: {
            people: {
              [constants.VIEW_MODE_CARD]: [
                { id: 'date_created', title: 'Date Created', visible: true },
                { id: 'language', title: 'Language', visible: true }
              ],

              [constants.VIEW_MODE_TABLE]: [
                { id: 'id', title: 'ID', visible: true },
                { id: 'timezone', title: 'Timezone', visible: true },
                { id: 'first_name', title: 'First Name', visible: true },
                { id: 'last_name', title: 'Last Name', visible: true },
                { id: 'primary_email', title: 'Email', visible: true },
                { id: 'date_created', title: 'Date Created', visible: true },
                { id: 'date_last_login', title: 'Last Login', visible: true }
              ]
            },

            org: {
              [constants.VIEW_MODE_CARD]: [
                { id: 'date_created', title: 'Date Created', visible: true }
              ],

              [constants.VIEW_MODE_TABLE]: [
                { id: 'id', title: 'ID', visible: true },
                { id: 'date_created', title: 'Date Created', visible: true },
                { id: 'name', title: 'Name', visible: true },
                { id: 'summary', title: 'Summary', visible: true },
                { id: 'tickets_count', title: 'Tickets', visible: true }
              ]
            }
          }
        }
      )
    }
  };
}

describe('CRM: CrmCardContainer', () => {
  const CrmCardContainer = require('~Card/CrmCardContainer').CrmCardContainer;
  const OrganizationCard = require('~Card/OrganizationCard').OrganizationCard;
  const PersonCard       = require('~Card/PersonCard').PersonCard;

  const render = (content) => {
    renderInCrmApp(fakeState(content), <CrmCardContainer />);
  };

  it('should render OrganizationCard if selected content is organizations', () => {
    spyOn(OrganizationCard.prototype, 'render').and.callThrough();
    spyOn(PersonCard.prototype, 'render').and.callThrough();

    render('Organization');

    expect(OrganizationCard.prototype.render).toHaveBeenCalled();
    expect(PersonCard.prototype.render).not.toHaveBeenCalled();
  });

  it('should render PersonCard, not OrganizationCard by default', () => {
    spyOn(OrganizationCard.prototype, 'render').and.callThrough();
    spyOn(PersonCard.prototype, 'render').and.callThrough();

    render();

    expect(PersonCard.prototype.render).toHaveBeenCalled();
    expect(OrganizationCard.prototype.render).not.toHaveBeenCalled();
  });
});
