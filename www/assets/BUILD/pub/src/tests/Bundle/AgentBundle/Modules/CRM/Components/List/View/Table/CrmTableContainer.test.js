// #define ~Table DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/List/View/Table

jest.dontMock('~Table/CrmTableContainer');

import React from 'react';
import { renderInCrmApp } from '../../../../crm.test-helper';
import { toImmutable } from 'Helpers';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

describe('CRM: CrmTableContainer', () => {
  const CrmTableContainer  = require('~Table/CrmTableContainer').CrmTableContainer;
  const OrganizationsTable = require('~Table/OrganizationsTable').OrganizationsTable;
  const PeopleTable        = require('~Table/PeopleTable').PeopleTable;
  const fakeRecords        = [{ id: 1 }, { id: 2 }, { id: 3 }];

  const fakeState = (content) => ({
    RecordsStore: {
      store: toImmutable({
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
      })
    },

    CRM: {
      list: toImmutable({
        elements:          [1, 2, 3],
        view:              constants.VIEW_MODE_TABLE,
        currentListParams: { content }
      })
    }
  });

  const render = (content) => {
    renderInCrmApp(fakeState(content), <CrmTableContainer />);
  };

  it('should render OrganizationCard if selected content is organizations', () => {
    spyOn(OrganizationsTable.prototype, 'render').and.callThrough();
    spyOn(PeopleTable.prototype, 'render').and.callThrough();

    render('organizations');

    expect(OrganizationsTable.prototype.render).toHaveBeenCalled();
    expect(PeopleTable.prototype.render).not.toHaveBeenCalled();
  });

  it('should render PersonCard, not OrganizationCard by default', () => {
    spyOn(OrganizationsTable.prototype, 'render').and.callThrough();
    spyOn(PeopleTable.prototype, 'render').and.callThrough();

    render();

    expect(PeopleTable.prototype.render).toHaveBeenCalled();
    expect(OrganizationsTable.prototype.render).not.toHaveBeenCalled();
  });
});
