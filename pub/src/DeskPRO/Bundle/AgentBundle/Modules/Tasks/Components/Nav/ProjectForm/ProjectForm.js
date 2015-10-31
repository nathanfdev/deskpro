import React from 'react';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import { Header } from './Header';
import { FieldGroup } from './Fields/FieldGroup';
import { FullField } from './Fields/FullField';
import { FloatField } from './Fields/FloatField';
import { CollectionField } from './Fields/CollectionField';

export class ProjectForm extends React.Component {

  render() {
    return (
      <div className="sidebar-hover">
        <div className="dpw--popup-main">
          <Header>Project - Create New</Header>

          <form>
            <inpit name="projectId" type="hidden" />

            <div className="dpw--popup-content">

              <FieldGroup>
                <FullField title="Title">
                  <input name="title" type="text" placeholder="Title" />
                </FullField>
              </FieldGroup>

              <FieldGroup>
                <FloatField align="left">
                  <div className="dpw-quick-filter">
                    <div className="dpw-quick-filter-container">
                      <div className="dpw-quick-filter-icon"><i className="fa fa-filter" /></div>
                      <input type="text" placeholder="Quick Filter" />
                      <span className="dpw-quick-filter-clear-link"><i className="fa fa-times-circle"></i></span>
                    </div>
                  </div>
                </FloatField>

                <FloatField align="right">
                  <div className="dpw-popup-content-item-show-only-selected">
                    <a href="#" className="checkbox-link'">
                      <span>Show only Selected</span>
                      <span className="dpw--checkbox-boxy"><i className="fa fa-check" /></span>
                    </a>
                  </div>
                  <div className="dpw-popup-content-item-unassign-all">
                    <a href="#" className="checkbox-link">
                      <span>Unassign</span>
                      <span className="unassign-all-icon"><span /></span>
                    </a>
                  </div>
                </FloatField>
              </FieldGroup>

              <FieldGroup>
                <CollectionField>
                  <div part="title">
                    Agent <a href="#">Assign to me</a>
                  </div>
                  <div part="selectbox">
                    <Scrollable vertical />
                  </div>
                </CollectionField>

                <CollectionField title="Team">
                  <Scrollable vertical />
                </CollectionField>

                <CollectionField title="Department">
                  <Scrollable vertical />
                </CollectionField>
              </FieldGroup>

              <FieldGroup>
                <FullField>
                  <button type="submit" value="Save" className="dpw--popup-button">Save</button>
                </FullField>
              </FieldGroup>

            </div>
          </form>
        </div>
      </div>
    );
  }
}
