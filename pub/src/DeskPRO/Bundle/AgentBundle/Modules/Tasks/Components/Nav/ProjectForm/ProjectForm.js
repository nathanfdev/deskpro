import React from 'react';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import { Header } from './Header';
import { FieldGroup } from './FieldGroup';
import { FullField } from './FullField';
import { CollectionField } from './CollectionField';

export class ProjectForm extends React.Component {

  render() {
    return (
      <div className="sidebar-hover">
        <div className="dpmw--popup-main">
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
                <div className="dpw--popup-content-left">
                  <div className="dpw-quick-filter">
                    <div className="dpw-quick-filter-container">
                      <div className="dpw-quick-filter-icon"><i className="fa fa-filter" /></div>
                      <input type="text" placeholder="Quick Filter" />
                      <span className="dpw-quick-filter-clear-link"><i className="fa fa-times-circle"></i></span>
                    </div>
                  </div>
                </div>

                <div className="dpmw--popup-content-right">
                  <div className="dpw-popup-content-item">
                    <div className="dpw-popup-content-item-show-only-selected">
                      <a href="#" className="checkbox-link'">
                        <span>Show only Selected</span>
                        <span className="dpw--checkbox-boxy"><i className="fa fa-check" /></span>
                      </a>
                    </div>
                  </div>
                  <div className="dpw-popup-content-item">
                    <div className="dpw-popup-content-item-unassign-all">
                      <a href="#" className="checkbox-link">
                        <span>Unassign</span>
                        <span className="unassign-all-icon"><span /></span>
                      </a>
                    </div>
                  </div>
                </div>
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
