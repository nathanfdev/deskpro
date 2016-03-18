import React, {Component, PropTypes} from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Popup, FieldGroup, DropdownList } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Popup';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { AgentsListContainer, TeamsListContainer, DepartmentsListContainer }
  from '../../../Common/Components/Form/Lists';
import { paramsSelector } from '../../../Application/Selectors/massActions';

import { connect } from 'react-redux';
@connect(state => ({
  currentParams: paramsSelector(state),
  languages: collectionSelectorFactory('Language', 'all')(state),
  categories: collectionSelectorFactory('TicketCategory', 'tickets')(state),
  workflows: collectionSelectorFactory('TicketWorkflow', 'tickets')(state)
}))
export class MassActionContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    navItem: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired,
    currentParams: PropTypes.object.isRequired,
    languages: PropTypes.object.isRequired,
    categories: PropTypes.object.isRequired,
    workflows: PropTypes.object.isRequired,
    actions: PropTypes.array.isRequired
  };

  componentWillMount() {
    this.setState({
      expanded: false
    });
  }

  onChange(param, value) {
    const { dispatch, currentParams } = this.props;
    const nextParams = currentParams.get('assign') ? currentParams.get('assign').toJS() : {};

    if (currentParams.get('assign') && currentParams.get('assign').get(param) && value.length < 1) {
      delete nextParams[param];
    } else {
      nextParams[param] = value;
    }
  }

  toggleExpanded = (event) => {
    event.preventDefault();
    this.setState({ expanded: !this.state.expanded });
  };
  collapse = () => this.setState({ expanded: false });

  render() {
    const { currentParams, categories, languages, workflows } = this.props;
    const assign = currentParams.get('assign');
    return (
      <ul className="dpwd-navigation-dropdown-top-row-main-list">
        <li>
        <span className="dpwd-navigation-dropdown-top-row-action-button" ref="button">
          <a href="" className="top-row-action-button-link" onClick={this.toggleExpanded}>
            <span
              className="dpwd-navigation-dropdown-top-row-button-text dpwd-navigation-dropdown-top-row-button-text-grey">
              Mass actions
            </span>
            <span className="top-row-action-button-link-extra">
              <span className="dpwd-navigation-dropdown-top-row-button-icon">
                <i className="fa fa-caret-down"></i>
              </span>
            </span>
          </a>
        </span>
          <Detached isOpen={this.state.expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.button}>
            <ClickOut onClickOut={this.collapse}
                      ignoreNodes={[this.refs.menuItem, '.dpw-navigation-dropdown-panel']}
                      additionalNodes={['.dpw-navigation-dropdown-item-clear']}>
              <div className="dpw-navigation-dropdown-panel">
                <Popup>
                  <form>
                    <div className="dpw--popup-content">
                      <div className="dpw--popup-item-collection">
                        <FieldGroup>
                          <h2 className="dpw--popup-item-section-title">Change Status</h2>
                        </FieldGroup>
                        <FieldGroup>
                          <AgentsListContainer selected={assign && assign.get('agent')}
                                               onChange={this.onChange.bind(this, 'agent')}/>
                          <TeamsListContainer selected={assign && assign.get('team')}
                                              onChange={this.onChange.bind(this, 'team')}/>
                          <DepartmentsListContainer selected={assign && assign.get('department')}
                                                    onChange={this.onChange.bind(this, 'department')}/>
                        </FieldGroup>
                        <FieldGroup>
                          <div className="dpmw--popup-content-full">
                            <h2 className="dpw--popup-item-section-title">Followers</h2>
                          </div>
                        </FieldGroup>
                        <FieldGroup>
                          <div className="dpw--popup-content-left even">
                            <h2 className="dpw--popup-item-section-title">Product</h2>

                            <div className="dpw--popup-form-container">
                            </div>
                          </div>
                          <DropdownList title="Workflow" items={workflows} option="title"/>
                          <DropdownList title="Language" items={languages} option="locale"/>
                          <DropdownList title="Category" items={categories} option="title"/>
                        </FieldGroup>
                        <FieldGroup>
                          <div className="dpmw--popup-content-full">
                            <h2 className="dpw--popup-item-section-title">Mass reply</h2>

                            <div className="dpw--popup-form-container">
                              <div className="dpw--popup-form-textarea">
                                <textarea>Text</textarea>
                              </div>
                            </div>
                          </div>
                        </FieldGroup>
                        <FieldGroup>
                          <div part="title">Other properties</div>
                        </FieldGroup>
                      </div>
                    </div>
                  </form>
                </Popup>
              </div>
            </ClickOut>
          </Detached>
        </li>
      </ul>
    );
  }
}