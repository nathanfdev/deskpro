import React, { PropTypes } from 'react';
import { MenuWrapper, Menu, MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import classNames from 'classnames';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';

export class VariablesMenuContainer extends React.Component {
  render() {
    return <VariablesMenu />;
  }
}

export class VariablesMenu extends React.Component {
  static propTypes = {
    viewModel:    PropTypes.object,
    onChangeMenu: PropTypes.func
  };
  static defaultProps = {
    onChangeMenu() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedLeft: null,
      filter:       ''
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      selectedLeft: nextProps.viewModel.first()
    });
  }

  setActive = (item) => {
    this.props.onChangeMenu(item);
    this.setState({
      selectedLeft: item
    });
  };

  getVariables = () => {
    if (!this.props.viewModel) {
      return null;
    }
    return this.props.viewModel.valueSeq().map((variable, key) =>
      <MenuItem
        key={`email${key}`}
        label={variable.get('description')}
        icon="folder open"
        className={classNames({ active: this.isActive(variable) })}
        onClick={() => this.setActive(variable)}
      />
    );
  };

  getRightPanel = () => {
    if (!this.state.selectedLeft || !this.state.selectedLeft.get('properties')) {
      return null;
    }
    const properties = this.state.selectedLeft.get('properties').valueSeq().map(
      (property, key) => {
        if (
          this.state.filter
          && property.get('description').toLowerCase().indexOf(this.state.filter.toLowerCase()) === -1
          && property.get('attribute').toLowerCase().indexOf(this.state.filter.toLowerCase()) === -1
          && this.state.selectedLeft.get('attribute').toLowerCase().indexOf(this.state.filter.toLowerCase()) === -1
        ) {
          return null;
        }
        return (<MenuItem key={key}>
          <span className="description">{property.get('description')}</span>
          <br />
          <span className="variable-name">
            {'{{'} {this.state.selectedLeft.get('attribute')}.{property.get('attribute')} {'}}'}
          </span>

        </MenuItem>);
      });
    return (
      <MenuWrapper className="right-panel">
        <Menu>
          {properties}
        </Menu>
      </MenuWrapper>
    );
  };

  updateFilter = (value) => {
    this.setState({
      filter: value
    });
  };

  isActive = item => item === this.state.selectedLeft;

  render() {
    return (
      <div className="variables two-panels-menu">
        <MenuWrapper>
          <SearchBox
            onUserInput={this.updateFilter}
          />
          <Menu>
            {this.getVariables()}
          </Menu>
          <footer>
            Enter a ticket ID for examples

          </footer>
        </MenuWrapper>
        {this.getRightPanel()}
      </div>
    );
  }
}
