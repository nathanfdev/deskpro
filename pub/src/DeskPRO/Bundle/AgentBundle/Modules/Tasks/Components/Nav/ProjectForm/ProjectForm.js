import React from 'react';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import { Header } from './Header';
import { FieldGroup } from './Fields/FieldGroup';
import { FullField } from './Fields/FullField';
import { FloatField } from './Fields/FloatField';
import { CollectionField } from './Fields/CollectionField';
import { QuickFilter } from './Fields/QuickFilter';
import { ShowOnlySelected } from './Fields/ShowOnlySelected';
import { Unassign } from './Fields/Unassign';

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
                  <QuickFilter />
                </FloatField>

                <FloatField align="right">
                  <ShowOnlySelected />
                  <Unassign />
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
