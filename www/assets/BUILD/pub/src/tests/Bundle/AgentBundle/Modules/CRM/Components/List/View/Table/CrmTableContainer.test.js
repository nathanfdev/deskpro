// #define ~Table DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/List/View/Table
// #define ~Card DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/List/View/Card

jest.dontMock('~Table/CrmTableContainer');
jest.dontMock('../Card/CrmCardContainer.test');

import React from 'react';
import { renderInCrmApp } from '../../../../crm.test-helper';

describe('CRM: CrmTableContainer', () => {
  const CrmTableContainer  = require('~Table/CrmTableContainer').CrmTableContainer;
  const OrganizationsTable = require('~Table/OrganizationsTable').OrganizationsTable;
  const PeopleTable        = require('~Table/PeopleTable').PeopleTable;
  const fakeState          = require('../Card/CrmCardContainer.test').fakeState;

  const render = (content) => {
    renderInCrmApp(fakeState(content), <CrmTableContainer />);
  };

  it('should render OrganizationCard if selected content is organizations', () => {
    spyOn(OrganizationsTable.prototype, 'render').and.callThrough();
    spyOn(PeopleTable.prototype, 'render').and.callThrough();

    render('Organization');

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
