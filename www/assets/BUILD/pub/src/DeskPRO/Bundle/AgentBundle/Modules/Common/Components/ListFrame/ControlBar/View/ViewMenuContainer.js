import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Button } from '../Button';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { ViewOptionsContainer } from './ViewOptionsContainer';
import { ViewModeMenu } from './ViewModeMenu';


@connect()
export class ViewMenuContainer extends Component {

  static propTypes = {
    dispatch:                PropTypes.func.isRequired,
    options:                 PropTypes.object.isRequired,
    viewMode:                PropTypes.string.isRequired,
    viewModeAction:          PropTypes.func.isRequired,
    onViewFieldsMenuUnmount: PropTypes.func,
    toggleFieldVisibility:   PropTypes.func,
    changeFieldOrder:        PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      expanded:        false,
      optionsExpanded: false
    };
  }

  expandMenu = () => this.setState({ expanded: true });
  expandOptions = () => this.setState({ optionsExpanded: true });
  collapse = () => this.setState({
    expanded:        false,
    optionsExpanded: false
  });

  render() {
    const { viewMode = '' } = this.props;

    return (
      <li>
        <Button isActive={this.state.expanded}
          onClick={this.expandMenu}
          ref="button"
          title="View:"
          icon={null}
          label={viewMode.charAt(0).toUpperCase() + viewMode.slice(1)}
        />

        <Detached isOpen={this.state.expanded} positionAt="left bottom" positionTarget={this.refs.button}>

          <ClickOut onClickOut={this.collapse} additionalNodes={[this.refs.viewModeMenu]}>
            {this.state.optionsExpanded
              ? <ViewOptionsContainer {...this.props} />
              : <div ref="viewModeMenu">
                  <ViewModeMenu {...this.props} expandOptions={this.expandOptions} />
                </div>
            }
          </ClickOut>
        </Detached>
      </li>
    );
  }
}

