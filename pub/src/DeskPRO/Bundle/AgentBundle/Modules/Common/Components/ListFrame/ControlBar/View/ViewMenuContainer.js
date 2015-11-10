import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { Button } from '../Button';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import ItemList from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/ItemList';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import { ViewField } from './ViewField';
import jQuery from 'jquery';

@connect()
export class ViewMenuContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    options: PropTypes.object.isRequired,
    viewMode: PropTypes.string.isRequired,
    viewModeAction: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      expanded: false,
      optionsExpanded: false
    };
  }

  expandMenu = () => this.setState({expanded: true});
  expandOptions = () => this.setState({optionsExpanded: true});
  collapse = () => this.setState({expanded: false, optionsExpanded: false});

  render() {
    const { dispatch, options, viewMode = '', viewModeAction } = this.props;

    return (
      <li>
          <Button
            onClick={this.expandMenu}
            ref="button"
            title="View:"
            icon={null}
            label={viewMode.charAt(0).toUpperCase() + viewMode.slice(1)}
          />

          <Detached isOpen={this.state.expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.button}>

            <ClickOut onClickOut={this.collapse}
                      additionalNodes={[this.refs.optionsButton]}>

              {this.state.optionsExpanded
                ? <ViewOptionsContainer {...this.props} />
                : <Menu>
                    {jQuery.map(options, (option, type) =>
                        <Item key={type}
                              label={option.label}
                              isActive={viewMode === type}
                              checked={viewMode === type}
                              onClick={() => dispatch(viewModeAction(type))}
                              icon={option.icon}/>
                    )}
                    <MenuFooter>
                      <div className="dpw-navigation-dropdown-options-link">
                        <a href="#"
                           ref="optionsButton"
                           onClick={this.expandOptions}>

                          View Options <i className="fa fa-cog"></i>
                        </a>
                      </div>
                    </MenuFooter>
                  </Menu>
              }
            </ClickOut>
          </Detached>
      </li>
    );
  }
}

@connect()
class ViewOptionsContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    viewMode: PropTypes.string.isRequired,
    options: PropTypes.object.isRequired
  };

  render() {
    const { viewMode, options, dispatch } = this.props;

    return (
      <Menu widgetClass="dpw-navigation-dropdown-secondary">
        {jQuery.map(options, (option, type) => {
          if (!option.configurableFields) {
            return null;
          }

          const onClick = name => {
            if (option.toggleFieldVisibility) {
              dispatch(option.toggleFieldVisibility(name));
            }
          };

          return (
            <Item discMarked
                  key={type}
                  label={option.label}
                  widgetClass="dpw-navigation-dropdown-column-list-item"
                  isActive={viewMode === type}
              >
              <ItemList>
                {jQuery.map(option.configurableFields, (label, name) =>
                    <ViewField
                      key={name}
                      value={name}
                      label={label}
                      isShown={(option.visibleFields || []).indexOf(name) > -1}
                      changeState={onClick} />
                )}
              </ItemList>
            </Item>
          );
        })}
      </Menu>
    );
  }
}
