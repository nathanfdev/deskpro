import React, { Component, PropTypes } from 'react';
import { Button } from '../Button';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import ItemList from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemList';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import { ViewField } from './ViewField';
import { connect } from 'react-redux';
import Immutable from 'immutable';

@connect()
export class ViewMenuContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    options: PropTypes.object.isRequired,
    viewMode: PropTypes.string.isRequired,
    viewModeAction: PropTypes.func.isRequired,
    tableFields: PropTypes.object.isRequired,
    tableVisibleFields: PropTypes.object.isRequired,
    tableToggleFieldVisibility: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      menuExpanded: false,
      optionsExpanded: false
    };
  }

  expandMenu = () => this.setState({menuExpanded: true});
  expandOptions = () => this.setState({optionsExpanded: true});
  collapse = () => this.setState({menuExpanded: false, optionsExpanded: false});

  render() {
    const { dispatch, options, viewMode, viewModeAction } = this.props;

    return (
      <li>
        <ClickOut onClick={this.expandMenu}
                  onClickOut={this.collapse}
                  ignoreNodes={[this.refs.menu, this.refs.options]}>
          <Button
            ref="button"
            title="View:"
            icon={null}
            label={viewMode.charAt(0).toUpperCase() + viewMode.slice(1)}
          />
          <Positioned isOpen={this.state.menuExpanded}
                      style={{display: this.state.optionsExpanded ? 'none' : 'block'}}
                      positionAt="left bottom"
                      positionTarget={this.refs.button}
                      ref="menu">
            <Menu>
              {options.map(option =>
                  <Item key={option.field}
                        label={option.label}
                        isActive={viewMode === option.field}
                        checked={viewMode === option.field}
                        onClick={() => dispatch(viewModeAction(option.field))}
                        icon={option.icon} />
              )}
              <MenuFooter>
                <div className="dpw-navigation-dropdown-options-link">
                  <a href="#" onClick={this.expandOptions}>View Options <i className="fa fa-cog"></i></a>
                </div>
              </MenuFooter>
            </Menu>
          </Positioned>
          <Positioned isOpen={this.state.optionsExpanded}
                      positionAt="left bottom"
                      positionTarget={this.refs.button}
                      ref="options">
            <ViewOptionsContainer {...this.props} />
          </Positioned>
        </ClickOut>
      </li>
    );
  }
}

@connect()
class ViewOptionsContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    viewMode: PropTypes.string.isRequired,
    tableConfigurableFields: PropTypes.object.isRequired,
    tableVisibleFields: PropTypes.any.isRequired,
    tableToggleFieldVisibility: PropTypes.object.isRequired,
    cardConfigurableFields: PropTypes.object.isRequired,
    cardVisibleFields: PropTypes.any.isRequired,
    cardToggleFieldVisibility: PropTypes.object.isRequired
  };

  render() {
    const {
      viewMode,
      tableConfigurableFields, tableToggleFieldVisibility,
      cardConfigurableFields, cardToggleFieldVisibility
    } = this.props;

    let { tableVisibleFields, cardVisibleFields } = this.props;

    if (Immutable.Iterable.isIterable(tableVisibleFields)) {
      tableVisibleFields = tableVisibleFields.toJS();
    }
    if (Immutable.Iterable.isIterable(cardVisibleFields)) {
      cardVisibleFields = cardVisibleFields.toJS();
    }

    return (
      <Menu widgetClass="dpw-navigation-dropdown-secondary">
        <Item discMarked
              label="Card view"
              widgetClass="dpw-navigation-dropdown-column-list-item"
              isActive={viewMode === 'card'}
          >
          <ItemList>
            {Object.keys(cardConfigurableFields).map(name =>
                <ViewField
                  key={name}
                  value={name}
                  label={cardConfigurableFields[name]}
                  isShown={cardVisibleFields.indexOf(name) > -1}
                  changeState={(field) => this.props.dispatch(cardToggleFieldVisibility(field))} />
            )}
          </ItemList>
        </Item>
        <Item discMarked
              label="Table view"
              widgetClass="dpw-navigation-dropdown-column-list-item"
              isActive={viewMode === 'table'}
          >
          <ItemList>
            {Object.keys(tableConfigurableFields).map(name =>
              <ViewField
                key={name}
                value={name}
                label={tableConfigurableFields[name]}
                isShown={tableVisibleFields.indexOf(name) > -1}
                changeState={(field) => this.props.dispatch(tableToggleFieldVisibility(field))} />
            )}
          </ItemList>
        </Item>
      </Menu>
    );
  }
}
