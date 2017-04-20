import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { MenuWrapper, Menu, MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import classNames from 'classnames';
import SearchBox from 'DeskPRO/Component/Semantic/SearchBox';
import * as actions from '../../Actions/templatesActions';

class VariablesMenuProperty extends React.Component {
  static propTypes = {
    property:         PropTypes.object,
    attribute:        PropTypes.string,
    onSelectVariable: PropTypes.func,
    getExample:       PropTypes.func,
  };

  selectVariable = (variable) => {
    this.props.onSelectVariable(variable);
  };

  render() {
    const { property, attribute } = this.props;
    let variableName = attribute ? `${attribute}.` : '';
    variableName += property.get('attribute');
    return (<MenuItem onClick={() => this.selectVariable(variableName)}>
      <span className="description">{property.get('description')}</span>
      <br />
      <span className="variable-name">
        {'{{'} {variableName} {'}}'}
      </span>
      { this.props.getExample(attribute, property.get('attribute')) }

    </MenuItem>);
  }
}

@connect(state => ({
  emailTemplates: state.EmailTemplates.templates
}))
export class VariablesMenuContainer extends React.Component {
  static propTypes = {
    dispatch:       PropTypes.func,
    emailTemplates: PropTypes.object.isRequired,
    closeMenu:      PropTypes.func,
    insertVariable: PropTypes.func,
  };
  static defaultProps = {
    insertVariable() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      exampleLoading: false,
      exampleError:   false,
    };
  }

  setTicketId = (ticketId) => {
    this.setState({
      exampleLoading: true
    });
    this.props.dispatch(actions.loadExampleTicket(ticketId)).then(
      () => {
        this.setState({
          exampleLoading: false
        });
      }
    ).catch(
      () => {
        this.setState({
          exampleLoading: false,
          exampleError:   false
        });
      }
    );
  };

  selectVariable = (variable) => {
    this.props.insertVariable(`{{ ${variable} }}`);
    setTimeout(() => this.props.closeMenu(), 100);
  };

  render() {
    let variables = null;
    let exampleTicket = null;
    if (this.props.emailTemplates) {
      variables = this.props.emailTemplates.get('variables');
      exampleTicket = this.props.emailTemplates.get('exampleTicket');
    }
    return (<VariablesMenu
      viewModel={variables}
      exampleTicket={exampleTicket}
      onSelectVariable={this.selectVariable}
      setTicketId={this.setTicketId}
      exampleLoading={this.state.exampleLoading}
      exampleError={this.state.exampleError}
    />);
  }
}

export class VariablesMenu extends React.Component {
  static propTypes = {
    viewModel:        PropTypes.object,
    exampleTicket:    PropTypes.object,
    onSelectVariable: PropTypes.func,
    setTicketId:      PropTypes.func,
    exampleLoading:   PropTypes.bool,
    exampleError:     PropTypes.bool,
  };
  static defaultProps = {
    setTicketId() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      selectedLeft: null,
      filter:       ''
    };
  }

  componentWillMount() {
    const button = window.document.getElementsByClassName('variables-button')[0];
    this.coverWidth = button.offsetWidth;
    this.selectFirstMenu();
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.viewModel !== this.props.viewModel) {
      this.setState({
        selectedLeft: nextProps.viewModel.first()
      });
    }
  }

  setActive = (item) => {
    this.setState({
      selectedLeft: item
    });
  };

  getVariables = () => {
    if (!this.props.viewModel) {
      return null;
    }
    return this.props.viewModel
      .filter(
        (variable) => {
          if (!this.state.filter) {
            return true;
          }
          if (variable.get('properties')) {
            return variable.get('properties')
                .filter(property => (property.get('description').toLowerCase().indexOf(this.state.filter.toLowerCase()) !== -1
                  || property.get('attribute').toLowerCase().indexOf(this.state.filter.toLowerCase()) !== -1))
                .size > 0;
          }
          return (variable.get('description').toLowerCase().indexOf(this.state.filter.toLowerCase()) !== -1
          || variable.get('attribute').toLowerCase().indexOf(this.state.filter.toLowerCase()) !== -1);
        }
      )
      .valueSeq().map((variable, key) =>
        <MenuItem
          key={`variable${key}`}
          label={variable.get('description')}
          icon="folder open"
          className={classNames({ active: this.isActive(variable) })}
          onClick={() => this.setActive(variable)}
        />
    );
  };

  getVariableExample = (property, attribute) => {
    let example = null;
    switch (property) {
      case 'ticket':
        if (this.props.exampleTicket) {
          example = this.props.exampleTicket.get(attribute);
        }
        break;
      default:
        return null;
    }
    if (typeof example !== 'undefined' && example !== null) {
      return <span className="example"> e.g. {example}</span>;
    }
    return null;
  };

  getRightPanel = () => {
    if (!this.state.selectedLeft) {
      console.log('Nothing selected');
      return null;
    }
    let properties = [];
    if (this.state.selectedLeft.get('type').match(/^(object|array of objects)/)) {
      if (!this.state.selectedLeft.get('properties')) {
        console.log('No properties');
        return null;
      }
      properties = this.state.selectedLeft.get('properties').valueSeq()
        .filter(property => (!this.state.filter
          || property.get('description').toLowerCase().indexOf(this.state.filter.toLowerCase()) !== -1
          || property.get('attribute').toLowerCase().indexOf(this.state.filter.toLowerCase()) !== -1))
        .sort((a, b) =>
          a.get('attribute').localeCompare(b.get('attribute'))
        )
        .map((property, key) => (<VariablesMenuProperty
          key={key}
          attribute={this.state.selectedLeft.get('attribute')}
          onSelectVariable={this.props.onSelectVariable}
          getExample={this.getVariableExample}
          property={property}
        />));
    } else {
      properties.push(<VariablesMenuProperty
        key="1"
        attribute=""
        onSelectVariable={this.props.onSelectVariable}
        getExample={this.getVariableExample}
        property={this.state.selectedLeft}
      />);
    }
    return (
      <MenuWrapper className="right-panel">
        <Menu>
          {properties}
        </Menu>
      </MenuWrapper>
    );
  };

  getLeftFooter = () => {
    if (this.state.selectedLeft && this.state.selectedLeft.get('attribute') === 'ticket') {
      return (<footer>
        Enter a ticket ID for examples <br />
        <div className="ui action input small">
          <input
            type="text"
            placeholder="ID"
            ref={(c) => { this.ticketIdField = c; }}
          />
          <button
            className={classNames(
              'ui button basic small',
              { loading: this.props.exampleLoading, error: this.props.exampleError }
            )}
            onClick={() => this.props.setTicketId(this.ticketIdField.value)}
          >
            Use
          </button>
        </div>
        <button
          className={classNames('ui button basic small')}
          onClick={this.clearTicketIdField}
        >
          Clear
        </button>

      </footer>);
    }
    return null;
  };

  clearTicketIdField = () => {
    this.ticketIdField.value = '';
    this.ticketIdField.focus();
  };

  selectFirstMenu = () => {
    if (this.props.viewModel) {
      this.setState({
        selectedLeft: this.props.viewModel.first()
      });
    }
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
        <div className="menu-button-cover" style={{ width: this.coverWidth + 20 }} />
        <MenuWrapper>
          <SearchBox
            onUserInput={this.updateFilter}
          />
          <Menu>
            {this.getVariables()}
          </Menu>
          {this.getLeftFooter()}
        </MenuWrapper>
        {this.getRightPanel()}
      </div>
    );
  }
}
