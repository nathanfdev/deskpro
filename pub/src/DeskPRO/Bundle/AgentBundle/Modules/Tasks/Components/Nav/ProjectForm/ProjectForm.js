import React, { PropTypes } from 'react';
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

  static propTypes = {
    agents: PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      title: '',
      quick_filter: '',
      filter_selected: false
    };
  }

  onChangeTitle = event => {
    this.setState({
      title: event.target.value
    });
  };

  onChangeQuickFilter = value => {
    this.setState({
      quick_filter: value
    });
  };

  onChangeFilterSelected = value => {
    this.setState({
      filter_selected: value
    });
  };

  onUnassignAll = () => {
    console.log('unassign all');
  };

  onSubmit = event => {
    event.preventDefault();
  };

  render() {
    return (
      <div className="sidebar-hover">
        <div className="dpw--popup-main">
          <Header>Project - Create New</Header>

          <form>
            <div className="dpw--popup-content">

              <FieldGroup>
                <FullField title="Title">
                  <input name="title"
                         type="text"
                         placeholder="Title"
                         value={this.state.title}
                         onChange={this.onChangeTitle} />
                </FullField>
              </FieldGroup>

              <FieldGroup>
                <FloatField align="left">
                  <QuickFilter value={this.state.quick_filter}
                               onChange={this.onChangeQuickFilter} />
                </FloatField>

                <FloatField align="right">
                  <ShowOnlySelected value={this.state.filter_selected}
                                    onChange={this.onChangeFilterSelected} />
                  <Unassign onClick={this.onUnassignAll} />
                </FloatField>
              </FieldGroup>

              <FieldGroup>
                <CollectionField>
                  <div part="title">
                    Agent <a href="#">Assign to me</a>
                  </div>
                  <Scrollable vertical>
                    <ul>
                      <li>
                        <a className="checkbox-button">
                          <span className="checkbox"><i className="fa fa-check"></i></span>
                          <span className="name">Agent 1</span>
                        </a>
                      </li>
                      <li>
                        <a className="checkbox-button">
                          <span className="checkbox"><i className="fa fa-check"></i></span>
                          <span className="name">Agent 2</span>
                        </a>
                      </li>
                      <li>
                        <a className="checkbox-button">
                          <span className="checkbox"><i className="fa fa-check"></i></span>
                          <span className="name">Agent 3</span>
                        </a>
                      </li>
                      <li>
                        <a className="checkbox-button">
                          <span className="checkbox"><i className="fa fa-check"></i></span>
                          <span className="name">Agent 4</span>
                        </a>
                      </li>
                      <li>
                        <a className="checkbox-button">
                          <span className="checkbox"><i className="fa fa-check"></i></span>
                          <span className="name">Agent 5</span>
                        </a>
                      </li>
                      <li>
                        <a className="checkbox-button">
                          <span className="checkbox"><i className="fa fa-check"></i></span>
                          <span className="name">Agent 6</span>
                        </a>
                      </li>
                      <li>
                        <a className="checkbox-button">
                          <span className="checkbox"><i className="fa fa-check"></i></span>
                          <span className="name">Agent 7</span>
                        </a>
                      </li>
                      <li>
                        <a className="checkbox-button">
                          <span className="checkbox"><i className="fa fa-check"></i></span>
                          <span className="name">Agent 8</span>
                        </a>
                      </li>
                    </ul>
                  </Scrollable>
                </CollectionField>

                <CollectionField title="Team">
                  todo
                </CollectionField>

                <CollectionField title="Department">
                  todo
                </CollectionField>
              </FieldGroup>

              <FieldGroup>
                <FullField>
                  <button type="submit"
                          value="Save"
                          className="dpw--popup-button"
                          onClick={this.onSubmit}>Save</button>
                </FullField>
              </FieldGroup>

            </div>
          </form>
        </div>
      </div>
    );
  }
}
